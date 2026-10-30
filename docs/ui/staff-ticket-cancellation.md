# Hủy vé nhân viên

Luồng `/nhan-vien/huy-ve` có bốn bước: tra cứu vé, xác nhận hủy, thanh toán, hoàn tất.
Bước xác nhận chỉ xem lại dữ liệu, chưa hủy vé. Bước thanh toán thực hiện yêu cầu máy chủ.

Nhân viên phải có `ticket.view` và `ticket.cancel`, tài khoản hoạt động, đã xác minh email,
và cùng nhà xe với vé. Trả tiền mặt yêu cầu thêm `payment.refund` và xác nhận đã trả tiền.
Không tự cấp quyền khi triển khai; cấp qua quản lý vai trò.

Dùng lại `CancellationPolicy` và `BookingPaymentResolver`, không thay đổi luồng hủy khách hàng.
Trên 24 giờ hoàn 90%; từ 4 đến 24 giờ hoàn 70%; dưới 4 giờ không hủy.
Vé đã đổi dùng mốc sớm hơn giữa giờ khởi hành gốc và giờ mới.

Tiền mặt: sau khi nhân viên xác nhận đã trả, hủy vé và ghi `refunded`, biên nhận CASH-REFUND,
người thực hiện và sự kiện audit trong cùng transaction. Không gọi ngân hàng.
Vé thanh toán trực tuyến có tài khoản: cho chọn hoàn tiền mặt tại quầy hoặc gửi yêu cầu
`refund_pending` vào hàng đợi hiện có. Vé không tài khoản chỉ hoàn tiền mặt tại quầy;
không tạo yêu cầu không có chủ tài khoản. Vé thanh toán tiền mặt chỉ hoàn tiền mặt.
Giữ nguyên giao dịch thanh toán gốc; lựa chọn tiền mặt được kiểm tra phía máy chủ và ghi audit.

Migration `2026_10_24_000002_support_staff_ticket_cancellations` cho phép tài khoản khách null,
thêm actor_id nullable để giữ tương thích với bản ghi khách hàng cũ. Luồng nhân viên luôn ghi actor_id.
Rollback bị chặn khi có bản ghi hủy nhân viên hoặc khách không tài khoản.

Vé/ghế/đơn, yêu cầu hoàn và audit được cập nhật nguyên tử. Khóa chuyến, đơn, vé và thanh toán;
kiểm tra lại số tiền, quyền, thời hạn và ngân sách hoàn. Yêu cầu lặp trả cùng kết quả.
Mất phản hồi giữ nguyên payload và chỉ cho thử lại; không trả tiền mặt lần thứ hai.
Báo giá thay đổi yêu cầu tải lại trước khi tiếp tục. Các vé khác trong đơn được giữ nguyên.

Khách tại quầy nhận biên nhận qua luồng nhân viên; không sửa màn hình quản lý hoàn tiền hiện có.
Kiểm thử dùng SQLite riêng và giao dịch giả, không gửi tiền hoặc webhook thực.
