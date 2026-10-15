# Đổi vé miễn phí — MVP

## Quy định

- Vé còn hiệu lực, đơn đã xác nhận, chuyến còn trạng thái chờ khởi hành và nguồn thanh toán hợp lệ.
- Đổi tối đa một lần, kiểm tra cả vé gốc và vé thay thế; không đặt lại số lần đổi khi cấp mã vé mới.
- Chuyến cũ và mới còn ít nhất 4 giờ tại thời điểm xác nhận.
- Cùng nhà xe, cùng tuyến/chiều, loại dịch vụ, mẫu xe/sức chứa và giá vé; không thu phí hoặc chênh lệch.
- Chọn ghế và điểm đón/trả theo dữ liệu thực của chuyến mới. Điểm dừng không hoạt động không được chọn.
- Vé khứ hồi không được đổi sang chuyến chồng lấn lượt còn lại.
- Khi hủy vé thay thế, dùng giờ sớm hơn giữa chuyến gốc và chuyến mới để tính phí/hạn hủy.

Khách mở **Lịch sử mua vé → Thao tác → Đổi vé**. Wizard gồm chọn vé, chọn chuyến/ghế/điểm, xác nhận và kết quả.
Admin có mục **Đổi vé miễn phí**; nhân viên vào **Xử lý vé → Đổi vé**.
Tài khoản quản lý/nhân viên cần đang hoạt động, xác minh email, có `ticket.view` và `ticket.exchange`, đúng phạm vi nhà xe.
Quản trị cấp quyền qua danh mục quyền hiện có. Nhân viên không được tự cấp quyền.

## Giao dịch và giữ ghế

Payment sở hữu policy, giữ ghế, phát hành thay thế và liên kết bằng chứng thanh toán. Controller dùng FormRequest.
Các Model TicketExchange, Ticket, Booking và Payment khai báo quan hệ Eloquent tương ứng.

Giữ ghế mới tối đa 5 phút; vé và ghế cũ vẫn có hiệu lực trong lúc lựa chọn.
Khách đóng wizard hoặc quay lại bước chọn thì gửi yêu cầu bỏ giữ. Rời trang gửi yêu cầu giữ kết nối;
nếu mạng không gửi được, thời hạn máy chủ vẫn tự loại lượt giữ khỏi tồn ghế. Không cần scheduler để hết giữ.

Mua vé qua SePay, thu tiền mặt tại quầy, tìm chuyến và quản lý ghế cùng kiểm tra lượt giữ đổi vé.
Xác nhận khóa các chuyến theo thứ tự ID, rồi khóa đơn, vé và lượt giữ. Kiểm tra lại nguồn thanh toán, điều kiện,
giá, ghế và điểm dừng trước khi tạo đơn/vé mới và giải phóng ghế cũ trong một transaction.
Lỗi giữa chừng rollback toàn bộ. Token xác nhận dùng lại trả đúng kết quả đã lưu, không tạo vé hoặc khoản thu lần hai.

Lỗi/mất phản hồi khi xác nhận giữ lại token để thử lại, kể cả khi đồng hồ phía trình duyệt đã hết thời gian.
Chỉ máy chủ quyết định kết quả. Đóng sau một lần thử xác nhận sẽ tải lại lịch sử để tránh giữ màn hình cũ.

## Thanh toán, lịch sử và báo cáo

Không tạo Payment mới và không sửa số tiền/trạng thái giao dịch gốc. Đơn mới liên kết Payment gốc qua TicketExchange;
BookingPaymentResolver hỗ trợ cả thanh toán khứ hồi SePay và biên nhận thu tiền mặt tại quầy.
Chỉ nhận thanh toán SePay đã khớp hoặc biên nhận tiền mặt hoàn tất do luồng thu tiền của hệ thống tạo.
Tổng giá trị vé gốc không được vượt số tiền đã thu.

Vé gốc giữ bản ghi lịch sử, chuyển trạng thái dữ liệu `cancelled`, được phân biệt là **Đã đổi vé** qua bản ghi đổi.
Không tạo yêu cầu hoàn tiền cho lần đổi. QR cũ bị từ chối theo trạng thái vé và không được cung cấp lại khi tra cứu.
Vé mới có mã/QR riêng; vẫn có thể hủy theo chính sách áp dụng cho vé đã đổi.

Đơn gốc cập nhật số vé và giá trị còn phục vụ; dữ liệu vé gốc, Payment và snapshot đổi được giữ để đối soát.
Đơn chỉ chuyển sang đóng/hủy khi không còn vé active/used. Các vé khác và lượt khứ hồi còn lại không bị chuyển/hủy.
Báo cáo chuyến tính vé thay thế trên chuyến mới; doanh thu thu tiền vẫn dựa vào Payment duy nhất.
Lịch sử khách hàng và chi tiết theo dõi đặt vé hiển thị liên kết vé cũ/mới, thời điểm và phí 0đ;
quản lý xem thêm người thao tác. Snapshot lưu chuyến, ghế, điểm, giá và lý do của lần đổi.

## Triển khai

1. Sao lưu theo quy trình triển khai hiện có.
2. Chạy migration `2026_10_15_000002_create_ticket_exchanges.php` và `2026_10_15_000003_register_ticket_exchange_permission.php` trước mã ứng dụng mới.
3. Build frontend và biên dịch Blade; không đưa `.env` hoặc build output vào Git.
4. Kiểm tra quyền nhân viên và chạy [UAT đổi vé](../testing/ticket-exchange-uat.md).

Migration chỉ thêm bảng/quyền, không sửa vé hoặc thanh toán hiện có. Không chạy seeder trên dữ liệu vận hành để kích hoạt tính năng.
Rollback bị chặn khi có lần đổi hoàn tất: phải giữ bản lưu audit và lập phương án phục hồi quan hệ vé trước khi gỡ bảng/quyền.

## Kiểm thử

- Unit: điều kiện vé, mốc đúng 4 giờ/dưới 4 giờ, khác tuyến/giá/hạng/sức chứa, vé đã đổi.
- Integration: quyền/phạm vi, giữ ghế hai chiều với luồng mua vé, thu tiền mặt, hết hạn/bỏ giữ, cấp vé nguyên tử,
  bấm lặp, rollback khi lỗi phát hành, đơn nhiều vé, khứ hồi, hủy sau đổi và nguồn thanh toán duy nhất.
- JavaScript: chọn/giữ/xác nhận, đồng ý chính sách, bỏ giữ, mất phản hồi và chống gửi lặp.
- UAT: người dùng chạy checklist trên trình duyệt. Test tự động không thay thế nghiệm thu giao diện,
  kiểm tra cạnh tranh thực tế trên MySQL hoặc xác minh chuyển tiền ngân hàng thật.
