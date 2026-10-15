# Kiểm thử chấp nhận đổi vé miễn phí

**Trạng thái: chờ người dùng chạy trên trình duyệt và xác nhận.**

## Kết quả tự động ngày 15/10/2026

- Toàn bộ PHPUnit sau sửa lỗi CI: **333 bài đạt, 3.392 assertions** trên MySQL 8.4.3 cô lập và SQLite cô lập.
- JavaScript wizard: **4 bài đạt**.
- Pint các file PHP/Blade thay đổi, kiểm tra cú pháp/diff, `npm.cmd run build`, `php artisan view:cache`: đạt.
- Code Quality Diff: **0 hard failures, 1 warning** ở lệnh xóa bảng trong `down()` của migration.
  Rollback này có kiểm tra chặn nếu tồn tại lần đổi hoàn tất, nhằm giữ audit và quan hệ vé.
- Chưa nghiệm thu bố cục trên trình duyệt do kết nối browser kiểm thử không khả dụng.
  Chưa kiểm thử hai phiên cạnh tranh đồng thời trên MySQL hoặc giao dịch ngân hàng thật; các mục UAT bên dưới vẫn chờ thực hiện.

Lỗi CI đã sửa: truy vấn JSON của MySQL cần ứng viên có kiểu JSON khi đối chiếu cột ID đơn;
test rollback dùng sự kiện truy vấn để tạo lỗi giữa giao dịch, thay cho trigger riêng của SQLite.
Ca hồi quy kiểm tra liên kết thanh toán tiền mặt khớp đúng ID số, không nhận nhầm đơn khác hoặc ID kiểu chuỗi.

## Chuẩn bị

- Dùng môi trường kiểm thử và vé kiểm thử đã có bằng chứng thanh toán hợp lệ; không dùng vé đang phục vụ khách thật.
- Tài khoản khách sở hữu vé; một admin; một nhân viên có `ticket.view` + `ticket.exchange`; một nhân viên thiếu quyền;
  một tài khoản thuộc nhà xe khác.
- Ít nhất hai chuyến cùng nhà xe/tuyến/mẫu xe/hạng/sức chứa/giá, còn hơn 4 giờ và còn ghế.
- Thêm trường hợp một đơn hai vé, một đơn khứ hồi, chuyến hết chỗ, điểm dừng ngừng hoạt động.
- Vé mẫu 2.000đ chỉ đổi sang chuyến 2.000đ. Chuyến 300.000đ không phải chuyến tương đương của vé đó.
  Không cần đổi giá dữ liệu đang có để vượt điều kiện.
- Dữ liệu lịch hiện tại có nhóm `FUTA-HCM-DL`/`FUTA-DL-HCM`, Limousine 34 giường, giá 300.000đ với nhiều ngày chạy;
  kiểm tra lại ngày tương lai và tồn ghế tại thời điểm thực hiện. Không cần chuyển tiền thật để chạy bộ PHPUnit.

## Checklist

| Mã | Thao tác | Kết quả chấp nhận |
|---|---|---|
| U01 | Khách mở Lịch sử mua vé → Đổi vé | Modal bốn bước, giao diện cam/trắng, không mất dữ liệu trang nền |
| U02 | Chọn vé đủ điều kiện, ngày/chuyến mới | Chỉ chuyến tương đương; hết chỗ bị khóa; không có chuyến thì có lời giải thích |
| U03 | Chọn ghế Limousine 34 giường | Vị trí và mã ghế khớp sơ đồ xe; ghế bán/giữ không chọn được |
| U04 | Chọn điểm đón/trả | Chỉ điểm đang hoạt động; điểm trả sau điểm đón; giờ và địa chỉ thuộc chuyến mới |
| U05 | Tiếp tục tới xác nhận | Vé/ghế cũ vẫn có hiệu lực; ghế mới được giữ và có đồng hồ; phí/thanh toán thêm đều 0đ |
| U06 | Mở cùng chuyến ở phiên khác khi đang giữ | Ghế mới không mua/giữ được qua cả web và bán vé tại quầy |
| U07 | Quay lại hoặc đóng bằng ×/Escape trước xác nhận | Bỏ giữ ghế mới, giữ nguyên vé cũ; không phát sinh Payment hoặc yêu cầu hoàn tiền |
| U08 | Chờ hết thời hạn giữ | Không xác nhận lượt giữ hết hạn; vé cũ còn nguyên; người khác có thể chọn ghế mới |
| U09 | Xác nhận đổi | Có mã/QR mới, thông tin đúng; ghế cũ được giải phóng; vé cũ hết hiệu lực |
| U10 | Bấm xác nhận nhanh nhiều lần | Chỉ một lần đổi, một vé mới và một lượt chuyển ghế |
| U11 | Mất phản hồi sau xác nhận rồi thử lại | Trả lại đúng mã vé mới nếu đã hoàn tất; không tạo thêm vé/thu tiền |
| U12 | Xem lịch sử, tra cứu và theo dõi đặt vé | Vé mới đã thanh toán từ nguồn gốc; vé cũ ghi đã đổi, không cung cấp QR lên xe cũ |
| U13 | Thử đổi vé thay thế lần nữa | Nút bị khóa hoặc wizard giải thích không đủ điều kiện; gửi yêu cầu trực tiếp cũng bị chặn |
| U14 | Đổi một vé trong đơn hai vé | Chỉ vé chọn được thay thế; vé/ghế còn lại giữ nguyên; khoản thu gốc không đổi |
| U15 | Đổi một lượt vé khứ hồi | Lượt kia không đổi; chuyến chồng lấn thời gian bị từ chối |
| U16 | Hủy vé đã đổi | Báo giá theo giờ sớm hơn của chuyến gốc/mới; tạo yêu cầu hoàn từ cùng Payment gốc |
| U17 | Admin mở Đổi vé miễn phí, tra mã và hỗ trợ đổi | Cùng wizard, ghi đúng admin là người thao tác và đúng khách là chủ vé |
| U18 | Nhân viên đủ quyền vào Xử lý vé → Đổi vé | Chỉ tra/thao tác trong nhà xe mình; kết quả và lịch sử khớp bên khách/admin |
| U19 | Nhân viên thiếu quyền hoặc thuộc nhà xe khác | Có thông báo hệ thống/không tìm thấy trong phạm vi; yêu cầu gửi trực tiếp bị từ chối |
| U20 | Thay giá, xe, trạng thái chuyến/điểm sau khi giữ trong môi trường test | Xác nhận bị chặn, không mất vé cũ và không có vé mới phát hành dở |
| U21 | Hai khách/two tab giành cùng một ghế trên MySQL | Chỉ một lượt giữ/đổi thành công; bên còn lại nhận lỗi và chọn lại |
| U22 | Kiểm tra desktop, màn hình hẹp, bàn phím | Không tràn modal; nút ×/Quay lại hoạt động; lỗi dưới ô tương ứng, lỗi hệ thống dùng thông báo chung |
| U23 | Kiểm tra tài chính và báo cáo | Không có Payment mới; thu nhập không tăng do đổi; vé phục vụ được tính trên chuyến mới; audit có người/thời điểm/giá/ghế |

Các case đổi/hủy là thao tác thật trên dữ liệu của môi trường đang mở. Chỉ xác nhận sau khi kiểm tra đúng bộ dữ liệu test.
Không giả mã giao dịch ngân hàng để tạo bằng chứng thanh toán.

## Chạy kiểm thử tự động

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/Payment/TicketExchangePolicyTest.php tests/Feature/Payment/TicketExchangeTest.php
node --test tests/js/ticket-exchange-wizard.test.mjs
php vendor/phpunit/phpunit/phpunit
npm.cmd run build
php artisan view:cache
```

Ghi kết quả từng Uxx (đạt/không đạt), tài khoản vai trò, mã vé test và ảnh lỗi. Tránh đưa số tài khoản, thông tin khách hoặc khóa thanh toán vào báo cáo chia sẻ.
