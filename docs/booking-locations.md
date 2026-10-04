# Dữ liệu địa điểm đặt vé FUTA

## Nguồn dữ liệu trong hệ thống

1. `app/Data/futa_booking_locations.json`: các tỉnh/thành, khu vực, văn phòng và địa chỉ người dùng cung cấp từ giao diện FUTA. Hai danh sách điểm đi và điểm đến được giữ riêng.
2. `database/seeders/data/branch-offices.txt`: danh sách chi nhánh/văn phòng đã có trong repo, được nạp bởi `BranchOfficeSeeder`. Ô tìm kiếm bổ sung tên và địa chỉ từ các văn phòng đang hoạt động trong bảng `branch_offices`.
3. `routes` và `trips`: các thành phố, bến đi và bến đến của chuyến FUTA đang mở bán. Danh sách gợi ý tự bổ sung các điểm này khi quản trị viên thêm chuyến.

Nguồn tham khảo cho cách hiển thị và luồng đặt vé: [trang đặt vé FUTA](https://futabus.vn/dat-ve), [hướng dẫn đặt vé FUTA](https://futabus.vn/huong-dan-dat-ve-tren-web). Tài liệu báo cáo của dự án ghi rõ điểm đón/trả phải thuộc tuyến đã chọn; tài liệu không liệt kê toàn bộ điểm đón/trả của từng chuyến.

## Quy tắc sử dụng

- Tên một văn phòng trong danh bạ chỉ xác nhận văn phòng đó có trong dữ liệu chi nhánh. Nó **không xác nhận** mọi chuyến đều đón hoặc trả khách ở văn phòng đó.
- Kết quả tìm chuyến chỉ lấy chuyến FUTA đang mở bán, đúng ngày/tuyến và còn đủ ghế. Nếu khách chọn văn phòng cụ thể, tuyến phải có bến tương ứng trong `origin_station` hoặc `destination_station`.
- Nếu khách chọn khu vực cụ thể như Di Linh hoặc Quận 5, hệ thống không coi mọi chuyến trong tỉnh là chuyến có điểm đón/trả tại khu vực đó.
- Để công bố đầy đủ điểm đón/trả cho từng chuyến, cần bổ sung quan hệ từ chuyến/tuyến đến danh sách bến và văn phòng được phục vụ. Không suy diễn quan hệ này từ địa chỉ văn phòng.
- Mỗi khi có thay đổi mạng lưới thực tế, cập nhật danh bạ và dữ liệu tuyến/chuyến từ nguồn FUTA đã được xác minh. Không dùng dữ liệu `provinces.json` để khẳng định FUTA đang khai thác mọi tỉnh/thành trong tệp đó.
