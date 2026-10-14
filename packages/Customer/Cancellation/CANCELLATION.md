# Hủy vé và hoàn tiền thủ công

## Chính sách và luồng khách hàng

Chính sách được chốt ngày 13/10/2026: trên 24 giờ trước giờ khởi hành, phí hủy 10%; từ 4 đến 24 giờ (bao gồm đúng hai mốc), phí 30%; dưới 4 giờ không hủy. Quy tắc này thay thế mốc 24 giờ trong tài liệu khảo sát. Phí được làm tròn lên một đồng; tiền hoàn bằng giá vé đã ghi nhận trừ phí. Máy chủ tính lại khi xác nhận và yêu cầu kiểm tra lại nếu số tiền thay đổi.

Khách hủy từng vé trong modal 4 bước tại lịch sử mua vé. Chỉ vé đang hoạt động, thuộc đơn đã xác nhận và chuyến chưa khởi hành mới đủ điều kiện. Sau khi xác nhận hợp lệ, vé chuyển `cancelled`, ghế được giải phóng ngay, khoản hoàn chuyển `refund_pending`. Các vé khác tiếp tục có hiệu lực; đơn chỉ chuyển sang hủy khi không còn vé `active` hoặc `used`. Vé không tự khôi phục khi hoàn tiền chậm hoặc thất bại.

Thanh toán vẫn do SePay và webhook hiện có xác minh. Yêu cầu thủ công kiểm tra giao dịch đã khớp, đúng mã thanh toán và số tiền gốc; không đòi token refund, UUID ngân hàng sandbox hoặc cùng môi trường với API refund. Giao dịch lịch sử có môi trường chưa xác định vẫn có thể dùng nếu các bằng chứng thanh toán gốc hợp lệ. Vé lượt về dùng được khoản thanh toán khứ hồi đã xác minh.

## Quy trình nhân viên

Trang `/quan-tri/hoan-tien` có danh sách, số lượng theo trạng thái, bộ lọc và modal chi tiết. Cấp quyền `payment.view` để xem; cấp thêm `payment.refund` để xử lý. Người dùng không phải admin chỉ truy cập hồ sơ của nhà xe của mình; tài khoản bị vô hiệu hóa không được xử lý.

1. Mở hồ sơ và đối chiếu vé, khách hàng, giao dịch gốc, phí hủy và số tiền cần hoàn.
2. Nhấn **Nhận xử lý**. Trạng thái chuyển `refund_processing` và lưu nhân viên phụ trách. Nhân viên khác không thể nhận hoặc ghi nhận kết quả khi hồ sơ đang được người này xử lý.
3. Thực hiện chuyển tiền qua ngân hàng theo quy trình kế toán, kiểm tra đúng người nhận. Phần mềm không thực hiện lệnh chuyển tiền.
4. Sau khi ngân hàng xác nhận thành công, nhập đúng số tiền, mã giao dịch ngân hàng đầy đủ, ngày/giờ và ghi chú; xác nhận đã đối chiếu ngân hàng. Không dùng ảnh chụp hoặc lời xác nhận của khách để thay cho kết quả ngân hàng.
5. Hệ thống ghi audit, chuyển yêu cầu và vé sang `refunded`, gửi thông báo trong hệ thống/email. Nếu chưa thành công, nhập ghi chú và ghi nhận `refund_failed`; vé vẫn mất hiệu lực. Khi nhận lại hồ sơ này, phải kiểm tra lịch sử và đối soát kết quả cũ trước khi chuyển tiền thêm lần nữa.

Mã giao dịch được chuẩn hóa chữ hoa và không được dùng cho hai yêu cầu khác nhau. Nếu có nhiều ngân hàng, dùng mã đầy đủ kèm định danh ngân hàng để tránh mã rút gọn trùng nhau. Số tiền phải bằng khoản đã tính; thời điểm hoàn không được trước khi hủy hoặc ở tương lai. Hồ sơ có `version`, khóa dòng và audit theo phiên bản để chặn cập nhật cũ và xử lý trùng. Gửi lại đúng kết quả hoàn đã lưu không tạo sự kiện/thông báo mới. Không cung cấp thao tác sửa/xóa lịch sử hoặc sửa kết quả đã hoàn.

## Dữ liệu và trạng thái

- `ticket_cancellations`: vé, khách hàng, lý do, phiên bản chính sách, giá vé, phí và số tiền hoàn.
- `ticket_refunds`: giao dịch thanh toán gốc, số tiền, chế độ xử lý, trạng thái, nhân viên, phiên bản, mã/thời điểm/số tiền hoàn đã xác nhận.
- `ticket_refund_events`: người thực hiện, trạng thái trước/sau, phiên bản, ghi chú và thông tin xác nhận; chỉ thêm mới qua quy trình xử lý.

Trạng thái yêu cầu thủ công: `refund_pending` → `refund_processing` → `refunded` hoặc `refund_failed`. Trạng thái vé độc lập: `cancelled` → `refunded`. Khoản thanh toán gốc giữ nguyên số tiền đã thu và trạng thái thanh toán; không biến một khoản hoàn một phần thành hoàn toàn bộ payment.

Khách xem trạng thái trong lịch sử mua vé; modal cập nhật dữ liệu lưu trên máy chủ mỗi 10 giây. Thông báo hủy và hoàn thành hoàn tiền dùng queue cho email/thông báo hệ thống. Không có vận chuyển SMS/Zalo trong MVP này.

## Triển khai

Chạy hai migration bổ sung theo thứ tự:

```powershell
php artisan migrate --path=database/migrations/2026_10_13_000001_create_ticket_cancellations_and_refunds.php
php artisan migrate --path=database/migrations/2026_10_13_000002_add_manual_refund_workflow.php
```

Migration thứ hai chuyển các yêu cầu tự động còn `queued`, chưa có mã SePay và chưa có lỗi sang chờ xử lý thủ công. Những yêu cầu đã gửi SePay hoặc có kết quả không rõ được giữ nguyên chế độ cũ để tránh hoàn trùng; cần đối soát riêng. Rollback bị chặn khi đã có audit hoàn tiền; phải bảo toàn lịch sử tài chính trước khi gỡ schema.

Cấu hình thủ công mặc định:

```dotenv
SEPAY_REFUND_MODE=manual
SEPAY_REFUND_ENABLED=false
```

Giữ cấu hình thanh toán/ngân hàng/webhook hiện tại. Không cần cấu hình API refund sandbox cho luồng thủ công. Sau khi đổi cấu hình:

```powershell
php artisan config:clear
php artisan queue:work
```

Scheduler tự gọi API hoàn tiền đã được gỡ. Service và lệnh `tickets:process-refunds` cũ chỉ xử lý hồ sơ `automatic` khi chủ động bật chế độ `automatic` cùng cấu hình API cũ. Chúng luôn bỏ qua hồ sơ `manual`, kể cả khi bật cờ API. Không bật lại cho các giao dịch có kết quả chưa rõ trước khi đối soát.

## Kiểm thử

Test dùng SQLite cô lập, HTTP/notification giả lập, không chuyển tiền thật. Kiểm tra phí tại các mốc 24/4 giờ, quyền sở hữu, xác nhận, báo giá cũ, hủy một phần, vé khứ hồi, yêu cầu trùng; phân quyền nhân viên, phạm vi nhà xe, nhận xử lý cạnh tranh, phiên bản cũ, số tiền/thời điểm/mã giao dịch không hợp lệ, retry sau thất bại và chống ghi nhận trùng. Test cũng xác nhận hồ sơ thủ công không gọi SePay và bảo toàn coverage của tích hợp API cũ.

Các test không chứng minh tiền đã được chuyển tại ngân hàng hoặc độ chính xác bố cục trình duyệt. Nhân viên chịu trách nhiệm đối soát ngân hàng; cần kiểm tra giao diện trực tiếp khi có trình duyệt khả dụng.
