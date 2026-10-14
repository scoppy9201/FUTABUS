# Đề xuất hoàn thiện thu tiền mặt tại quầy

Trạng thái: chờ phê duyệt thay đổi API contract và một migration mới. Chưa sửa code chạy hoặc database cho yêu cầu này.

## Những gì đã xác minh

- `payments.method` đã hỗ trợ `cash`; không cần đổi enum.
- `CashSaleService` hiện dùng transaction, khóa chuyến, kiểm tra ghế/giá/phạm vi nhà xe, mã thanh toán duy nhất và tái sử dụng `TicketIssuer`.
- API tiền mặt hiện chỉ yêu cầu `received`; giao diện chưa có số tiền khách đưa, tiền thừa hoặc checkbox bắt buộc.
- `payments.booking_id` bắt buộc và có foreign key đến `bookings`. Bảng này không lưu được một khoản tiền đã nhận nhưng chưa phát hành được booking/vé.
- Transaction hiện rollback toàn bộ khi phát hành thất bại. Không có bảng lịch sử thu tiền mặt độc lập hoặc service đối soát tiền mặt được tìm thấy trong codebase.
- Trạng thái ghế của giao diện khách hàng được tính từ `booked_seats` và các khoản giữ ghế. Không cập nhật `seat_layouts.is_available` thành false: đó là cấu hình ghế của xe, không phải trạng thái ghế của một chuyến.
- Đã đọc các skill development, frontend, payment, testing; quy định security/database/architecture/testing/coding standards; tài liệu UC-13.1 được cung cấp và code liên quan.

## API đề xuất, chờ phê duyệt

Giữ endpoint `POST /api/v1/staff/sales/{sale}/cash-collections`. Giữ auth, CSRF, rate limit, permission `booking.create` và phạm vi nhà xe hiện có.

Request bắt buộc:

```json
{
  "tendered_amount": 2000000,
  "received": true
}
```

`tendered_amount` là số nguyên VND dương trong giới hạn lưu trữ hiện có. Không nhận `total`, `change`, `paid`, `status` từ client để xác định kết quả. Backend tính lại số tiền từ giá chuyến và ghế thực tế. Thiếu tiền hoặc chưa xác nhận nhận tiền trả 422, không tạo payment completed hoặc vé. Request cũ chỉ gửi `received` sẽ bị từ chối; cập nhật toàn bộ caller Staff và các test hiện có trong cùng thay đổi.

Thành công giữ envelope `data`, bổ sung `tendered_amount` và `change_amount` do Backend tính. Tổng tiền giữ ở `data.amount`, phương thức `cash`, trạng thái `paid` chỉ sau commit. UUID `sale` vẫn là khóa idempotency; request lặp cùng thông tin trả cùng kết quả, thay số tiền khách đưa trên cùng khoản thu đã ghi nhận trả 409.

`GET /api/v1/staff/sales/{sale}` và trang thanh toán Staff sẽ đọc thêm khoản thu chưa hoàn tất để trả trạng thái cần đối soát với mã tham chiếu. Lỗi nghiệp vụ sau xác nhận nhận tiền trả 409, lỗi xử lý nội bộ trả 503, không trả thành công. Client kiểm tra lại mã khoản thu khi mất kết nối, không yêu cầu thu tiền lần nữa.

## Migration đề xuất, chờ phê duyệt

File mới dự kiến: `database/migrations/2026_10_13_000001_create_staff_cash_collections_table.php`.

Bảng mới: `staff_cash_collections`, thuộc FuteBus/StaffTicketProcessing:

| Cột | Mục đích |
| --- | --- |
| `id` | Khóa chính |
| `sale_token` UUID unique | Idempotency theo phiên bán vé |
| `operator_id`, `bus_company_id` | Người xác nhận nhận tiền và phạm vi nhà xe; foreign key, không cascade xóa lịch sử thu tiền |
| `amount`, `tendered_amount`, `change_amount` decimal(12,2) | Tổng vé chuẩn Backend, tiền khách đưa, tiền thừa |
| `received_at` | Thời gian nhân viên xác nhận đã nhận tiền |
| `status` | `received`, `completed`, `needs_review`; không tự thêm trạng thái hoàn tiền thành công |
| `payment_id` nullable | Liên kết payment sau khi phát hành thành công; foreign key, không cascade xóa lịch sử |
| `snapshot` JSON | Dữ liệu vé từ server để kiểm tra lại và hỗ trợ đối soát; không ghi snapshot vào log/chat |
| `failure_code` nullable | Mã lỗi ổn định; không chứa stack trace, thông tin khách hoặc secret |
| `created_at`, `updated_at` | Lịch sử thời điểm |

Không sửa migration cũ hoặc bảng Payment hiện tại. Migration mới chỉ tạo bảng, không cập nhật dữ liệu cũ. Rollback drop bảng chỉ dành cho môi trường chưa có khoản thu thực tế; không tự chạy rollback có dữ liệu. Triển khai bảng trước code phụ thuộc bảng. Không tự chạy migration trên database Laragon cho đến khi được phép áp dụng.

## Bảo vệ giao dịch

1. Kiểm tra permission, CSRF, nhà xe, ownership, request và số tiền. Tính lại tổng tiền tại Backend, so khớp báo giá server.
2. Nhân viên đã đánh dấu nhận đủ tiền: ghi nhận khoản thu `received` trong transaction riêng, trước bước phát hành. Unique sale_token bảo vệ ghi nhận lặp; khoản thu này không có nghĩa đã bán vé thành công.
3. Transaction phát hành khóa khoản thu và các chuyến theo thứ tự ID thống nhất. Kiểm tra lại giá, trạng thái chuyến, thời gian khởi hành, ghế và mọi hold cạnh tranh. Tái sử dụng `TicketIssuer` và `SeatReservationService`; payment cash, bookings, booked_seats, tickets và trạng thái completed của khoản thu commit cùng nhau.
4. Nếu phát hành thất bại, rollback transaction phát hành, giữ khoản thu đã nhận, ghi `needs_review` và failure_code. Khi DB chưa phục hồi để cập nhật lỗi, trạng thái `received` vẫn là bằng chứng khoản thu chưa hoàn tất; UI không báo thành công. Log kỹ thuật chỉ chứa mã khoản thu và mã lỗi.
5. Không tạo sự kiện/webhook SePay giả. Giữ cách xử lý bank intent đã được thay bằng tiền mặt và khoản chuyển khoản đến muộn qua quy trình đối soát SePay hiện có.
6. Chặn nút quay lại/xác nhận mới khi khoản thu còn cần đối soát để không làm mất tham chiếu hoặc thu tiền lần hai. Hiển thị hướng dẫn giữ biên nhận và liên hệ quản lý; chưa tự thực hiện hoàn trả hay tạo API hoàn tiền khi chưa có quy trình được duyệt.

## File dự kiến chỉnh sửa

- `packages/FuteBus/StaffTicketProcessing/src/Http/Controllers/Api/CounterSaleController.php`: nhận Form Request và trả kết quả/đối soát.
- `packages/FuteBus/StaffTicketProcessing/src/Http/Requests/CashCollectionRequest.php`: mới, validation tiền khách đưa và xác nhận đã nhận.
- `packages/FuteBus/StaffTicketProcessing/src/Services/CashSaleService.php`: bổ sung ghi nhận khoản thu, kiểm tra số tiền, idempotency, phát hành nguyên tử và trạng thái đối soát; giữ tái sử dụng issuer hiện có.
- `packages/FuteBus/StaffTicketProcessing/src/Services/StaffPaymentPresenter.php`, `Http/Controllers/StaffPaymentController.php`: hiển thị khoản thu thành công hoặc cần đối soát.
- `packages/FuteBus/StaffTicketProcessing/src/resources/views/partials/customer-payment.blade.php`: form tiền khách đưa, tiền thừa, checkbox và nút xác nhận; giữ bố cục ba cột.
- `packages/FuteBus/StaffTicketProcessing/src/resources/views/partials/customer-success.blade.php`: nhãn Bán vé thành công và chi tiết biên nhận tiền mặt; giữ xem/in vé hiện có.
- `packages/FuteBus/StaffTicketProcessing/src/resources/views/partials/cash-review.blade.php`: mới, trạng thái cần đối soát và mã tham chiếu, không hiển thị vé đã phát hành giả.
- `packages/FuteBus/StaffTicketProcessing/src/resources/js/payment.js`: tính tiền thừa để hiển thị, khóa nút, gửi request và xử lý kết quả không rõ/đối soát; kết quả Backend là nguồn chính thức.
- `packages/FuteBus/StaffTicketProcessing/src/resources/lang/{vi,en}/sale.php`: toàn bộ thông báo/nhãn.
- Migration mới nêu trên; `docs/api/openapi.json`, `docs/api/README.md`: contract API.
- Test mới `tests/Feature/StaffTicketProcessing/CashCollectionTest.php`, đúng hai method; cập nhật payload các test tiền mặt cũ để giữ toàn bộ coverage.

Không dự kiến sửa `FuteBus/Payment`, Customer, cổng SePay, permission hoặc thêm thư viện/CSS mới. Nếu phân tích khi triển khai cho thấy cần mở rộng phạm vi, báo cáo trước.

## Kiểm thử sau phê duyệt

Hai automated test mới:

1. Nhân viên có quyền, nhận đủ tiền, ghế hợp lệ: API paid sau commit; amount/tiền thừa tính từ Backend; payment cash completed; vé/ghế đã bán; operator và thời gian ghi nhận; retry không phát hành lần hai.
2. Tiền khách đưa thiếu: 422; không payment completed, không tickets/ghế bán; không có khoản thu xác nhận đủ tiền.

Giữ và chạy các test hiện có CounterSaleTest/CashSaleTest, SePayPaymentTest, API documentation. Bổ sung các assertion lỗi sau nhận tiền/ownership vào coverage hiện có phù hợp, không xóa test cũ. Chạy Pint trên PHP thay đổi, Blade cache và Vite build. SQLite xác minh các chuyển trạng thái; không tuyên bố nó chứng minh cạnh tranh MySQL hoặc giao dịch tiền thực tế. Không commit/push.
