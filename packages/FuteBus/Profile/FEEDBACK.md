# Phản ánh khách hàng

MVP thuộc module Profile. Khách đăng nhập mở mục **Phản ánh** trong tài khoản để gửi sự việc, đính kèm ảnh, chọn chuyến liên quan và theo dõi kết quả xử lý.

## Tạo phản ánh theo 4 bước

Bấm **Tạo phản ánh** để mở modal. Mỗi màn hình hiển thị một bước:

1. **Vấn đề**: chọn loại sự việc và mức độ.
2. **Chuyến đi**: chọn chuyến thuộc tài khoản; có thể tiếp tục khi không chọn chuyến.
3. **Nội dung**: nhập tiêu đề, mô tả và chọn ảnh minh chứng nếu có.
4. **Liên hệ**: kiểm tra họ tên, số điện thoại, email; xác nhận thông tin trung thực và chọn quyền liên hệ rồi gửi.

**Tiếp tục** kiểm tra các ô của bước hiện tại. **Quay lại** giữ nguyên dữ liệu và ảnh đã chọn. Nút **Gửi phản ánh** xuất hiện ở bước cuối; trước khi gửi, toàn bộ ô bắt buộc được kiểm tra, kể cả các bước đang ẩn. Nếu server trả lỗi, modal mở lại ở bước chứa ô lỗi đầu tiên và giữ nội dung đã nhập; khách cần chọn lại ảnh sau request lỗi.

Tiến trình có nhãn bước hiện tại cho trình đọc màn hình. Nội dung từng bước cuộn trong modal khi màn hình thấp; trên điện thoại, hướng dẫn phụ được thay bằng liên kết tổng đài gọn. Khi JavaScript chưa tải, form vẫn cho phép nhập và gửi đầy đủ các trường.

## Dữ liệu và luồng xử lý

- `customer_feedback_reports`: chủ tài khoản, loại sự việc, mức độ, mã chuyến snapshot, nội dung, thông tin liên hệ, xác nhận, trạng thái, phản hồi và thời điểm xử lý.
- `customer_feedback_attachments`: đường dẫn ảnh riêng tư, tên gốc, MIME và dung lượng. Ảnh nằm trên disk `local`, được trả qua route kiểm tra quyền sở hữu.
- Một phản ánh mới có trạng thái `received`. Quy trình tiếp theo là `reviewing` → `resolved`. Ngày xử lý chỉ được ghi khi chuyển bước; sửa phản hồi tại cùng bước giữ ngày cũ.
- Dữ liệu phản ánh được lưu trong hai bảng này. Liên hệ công khai và đánh giá chuyến đi giữ luồng nghiệp vụ riêng.

## Điểm tích hợp quản trị

`FuteBus\Profile\Services\FeedbackService::updateStatus($admin, $reportId, $status, $response)` là điểm cập nhật dùng cho phần quản trị khi triển khai sau.

Service kiểm tra `isAdmin()`, khóa bản ghi trong transaction, từ chối lùi/bỏ bước và giới hạn phản hồi 3000 ký tự. Khách chỉ đọc trạng thái/phản hồi của chính mình; dữ liệu POST của khách không được điều khiển các trường xử lý này.

Danh mục mới: `staff`, `trip_service`, `schedule`, `booking_payment`, `lost_property`, `other`. Mức độ: `suggestion`, `complaint`, `urgent`. Giá trị `pickup` vẫn được nhận và hiển thị cho dữ liệu cũ.

## Route khách hàng

| Route name | Chức năng |
| --- | --- |
| `profile.feedback.index` | Danh sách, tìm theo tiêu đề/mã chuyến/mã PA, lọc trạng thái, phân trang |
| `profile.feedback.store` | Gửi phản ánh, giới hạn 5 lần/phút |
| `profile.feedback.show` | Chi tiết; `?modal=1` trả partial cho dialog |
| `profile.feedback.attachment` | Trả ảnh theo ID phản ánh và ID ảnh; có kiểm tra quyền |

## Triển khai

Áp dụng lần lượt hai migration bổ sung:

```sh
php artisan migrate --path=database/migrations/2026_10_11_000003_create_customer_feedback_reports_table.php
php artisan migrate --path=database/migrations/2026_10_12_000001_extend_customer_feedback_reports.php
npm run build
php artisan view:cache
```

Disk `local` cần ghi được. Cấu hình PHP/web server phải chấp nhận mỗi ảnh 5 MB và request chứa tối đa 3 ảnh (nên đặt `post_max_size` ít nhất 16 MB). Laravel vẫn kiểm tra MIME thực, ảnh hợp lệ và dung lượng; ảnh có tên JPG nhưng nội dung khác sẽ bị từ chối.

## Kiểm tra

```sh
php artisan test tests/Feature/Profile
node --test tests/js/feedback-images.test.mjs tests/js/feedback-wizard.test.mjs
```

Các test phản ánh kiểm tra lưu dữ liệu, validation, quyền sở hữu, phản hồi và ngày xử lý, XSS, giới hạn gửi, ảnh riêng tư, MIME/dung lượng/số lượng và rollback khi ghi ảnh lỗi.

Kiểm tra trình duyệt ở màn hình rộng và điện thoại: mở/đóng bằng nút, Escape và nền ngoài dialog; đi qua đủ 4 bước với hoặc không chọn chuyến; thử Tiếp tục khi nội dung chưa hợp lệ; quay lại và kiểm tra dữ liệu/ảnh còn nguyên; chọn/thả/xóa ảnh; gửi ở bước cuối và sửa ở đúng bước khi server trả lỗi; lọc và xem chi tiết; kiểm tra ảnh và phản hồi sau khi cập nhật trạng thái.
