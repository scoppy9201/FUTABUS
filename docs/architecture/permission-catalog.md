# Danh mục chức năng theo module

`RolePermission` tổng hợp quyền của các module đang được Laravel nạp. Database
`permissions` lưu ID, tên tùy chỉnh và trạng thái; database không xác định module
nào đang triển khai một chức năng.

## Nguồn quyền

- Middleware `permission:<slug>` trên route đã đăng ký được trích xuất tự động.
  Module sử dụng được xác định từ namespace của controller xử lý route.
- Với kiểm tra trong controller, request, service hoặc middleware chuyên biệt,
  module khai báo bổ sung tại `src/config/permissions.php`. Chỉ khai báo các quyền
  thực sự được backend kiểm tra. Không suy diễn quyền CRUD từ tên module.
- Chỉ đọc khai báo của module có service provider đang được nạp. Cùng một quyền
  được nhiều module sử dụng vẫn chỉ có một bản ghi, kèm danh sách module sử dụng.

`PermissionCatalogService` là nguồn chung cho giao diện, validation và seeder.
Danh sách cứng `config/permission_catalog.php` đã được thay thế.

## Kết quả rà soát

Danh mục hiển thị đúng 9 nhóm nghiệp vụ, với 33 quyền. File
`RolePermission/src/config/catalog-groups.php` quy định thứ tự nhóm và ánh xạ
nhóm vé vào đặt vé; các mã quyền vẫn được lấy từ module:

| Nhóm nghiệp vụ | Quyền được kiểm tra |
| --- | --- |
| Phương tiện | bus.view; bus.create; bus.update; bus.delete |
| Tuyến đường và điểm dừng | route.view; route.create; route.update; route.delete |
| Chuyến xe | trip.view; trip.create; trip.update; trip.delete; trip.cancel |
| Lịch trình xe | trip_schedule.view; trip_schedule.create; trip_schedule.update; trip_schedule.delete |
| Tài khoản nhân viên | user.view; user.create; user.update; user.delete |
| Quản lý vai trò | role.view; role.create; role.update; role.delete |
| Quản lý hoàn tiền | payment.view; payment.refund |
| Đặt vé | booking.view; booking.create; ticket.view |
| Hỗ trợ khách hàng | support.view; support.respond; support.assign |

Quyền phương tiện bao phủ loại phương tiện, thông tin xe, loại giấy tờ và hồ sơ
giấy tờ. Đổi trạng thái/gia hạn dùng `bus.update`; ngừng dùng/xóa dùng `bus.delete`.
Các thao tác điểm dừng hiện dùng `route.update`, xem dùng `route.view`.
Xóa chuyến chưa gán xe dùng `trip.delete`; hủy chuyến đã lên lịch dùng `trip.cancel`.

Phương tiện, chuyến xe và lịch trình xe vẫn có điều kiện tài khoản Admin ở
controller/request. Các nhãn yêu cầu Admin đã được bỏ khỏi giao diện theo yêu
cầu; cấp quyền cho nhân viên không tự mở các màn hình đó. Không nới điều kiện
Admin khi chưa triển khai cách ly dữ liệu theo nhà xe cho các luồng này.

Các quyền tổng quan, báo cáo và khách hàng ngoài phạm vi 9 nhóm vẫn được giữ
trong nguồn module/database, nhưng không xuất hiện trong danh sách cấp mới này.
`ticket.view` hiển thị trong nhóm Đặt vé, còn mã quyền và nhóm database vẫn là
`ticket.view`/`ticket` để giữ tương thích các nơi kiểm tra quyền.

Menu Xử lý vé bị khóa khi nhân viên không có quyền đang hoạt động nào trong
`booking.create`, `ticket.view` của nhà xe mình. `booking.view` chỉ mở Theo dõi
đặt vé, không mở Xử lý vé. Bấm mục khóa
hiện thông báo qua FutaNotify; truy cập trực tiếp trang xử lý vé bị backend chặn
với HTTP 403. Có một quyền trong nhóm sẽ mở trang; quyền bán vé, xem danh sách
vé và xem đơn đặt vé vẫn được kiểm tra riêng ở từng nghiệp vụ.

Các mục Hỗ trợ khách hàng, Tra cứu vé, Theo dõi đặt vé và Quản lý hoàn tiền
dùng chung giao diện khóa: thiếu lần lượt `support.view`, `ticket.view`,
`booking.view`, `payment.view` thì hiển thị nút có biểu tượng khóa và thông báo
FutaNotify khi bấm. Không chuyển tới giao diện khi thiếu quyền; route backend
vẫn kiểm tra quyền và trả HTTP 403 nếu truy cập trực tiếp.

## Đồng bộ và triển khai

1. Triển khai mã nguồn và build frontend; làm mới config/route cache theo quy trình
   triển khai hiện có để bỏ cấu hình cũ và nạp các route mới.
2. Admin mở **Quản lý quyền truy cập → Danh mục chức năng**. Chức năng đã được
   module khai báo nhưng chưa có trong database hiển thị **Chưa đồng bộ**.
3. Chọn **Đồng bộ danh mục** để thêm quyền còn thiếu. Thao tác có thể chạy lại,
   không cập nhật quyền đã tồn tại, không bật lại quyền đã tắt, không thay đổi
   `permission_role` hoặc `role_user` và không tự cấp quyền mới cho nhà xe.
4. Cấp quyền mới thông qua vai trò phù hợp sau khi đồng bộ.

GET danh mục không ghi database. Không cần migration hoặc reseed dữ liệu đang
hoạt động. Quyền cũ không còn có nguồn module vẫn được giữ trong database và các
liên kết hiện có; chúng không xuất hiện để cấp mới và không bị thu hồi tự động.
Sửa vai trò chỉ thay thế các quyền đang hoạt động trong danh mục; giữ nguyên
liên kết với quyền đã tắt và quyền ngoài phạm vi 9 nhóm đang không hiển thị.

**Vô hiệu hóa quyền** áp dụng toàn hệ thống ở lần kiểm tra quyền tiếp theo.
Admin vẫn vượt qua kiểm tra theo cơ chế hiện có trong `User::hasPermissionTo`.

## Bổ sung chức năng

Đặt kiểm tra quyền tại backend của module. Nếu dùng middleware `permission:`,
danh mục sẽ tự phát hiện route mới. Với kiểm tra khác, bổ sung khai báo trong
`src/config/permissions.php` của module, ví dụ:

```php
return ['report' => ['view']];
```

Thêm bản dịch tên nhóm/quyền và tên module trong ngôn ngữ của `RolePermission`,
chạy test phân quyền, rồi đồng bộ danh mục. Không khai báo quyền nếu backend chưa
kiểm tra quyền đó.
