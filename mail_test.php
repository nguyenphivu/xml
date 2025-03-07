<?php
// Cấu hình SMTP (không bắt buộc - chỉ khi bạn muốn dùng SMTP thay vì mail() function)
$smtp_config = [
    'enabled' => false,  // Đặt thành true để kích hoạt SMTP
    'host' => 'smtp.example.com',
    'port' => 587,
    'username' => 'your_username',
    'password' => 'your_password',
    'secure' => 'tls' // tls hoặc ssl
];

// Xử lý khi form được gửi
$message = '';
$alertClass = '';
$debug_info = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $from = filter_input(INPUT_POST, 'from', FILTER_SANITIZE_EMAIL);
    $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $content = $_POST['content'];
    $charset = $_POST['charset'];
    $encoding = $_POST['encoding'];
    $use_html = isset($_POST['use_html']) ? true : false;
    
    // Kiểm tra tính hợp lệ của email
    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email người gửi không hợp lệ!';
        $alertClass = 'alert-danger';
    } elseif (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email người nhận không hợp lệ!';
        $alertClass = 'alert-danger';
    } else {
        // Thiết lập các headers
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        // Xác định content type (plain text hoặc HTML)
        $contentType = $use_html ? "text/html" : "text/plain";
        
        // Xử lý nội dung theo encoding được chọn
        if ($encoding == 'base64') {
            $encodedContent = chunk_split(base64_encode($content));
            $headers .= "Content-Type: $contentType; charset=$charset\r\n";
            $headers .= "Content-Transfer-Encoding: base64\r\n";
        } else if ($encoding == 'quoted-printable') {
            $encodedContent = quoted_printable_encode($content);
            $headers .= "Content-Type: $contentType; charset=$charset\r\n";
            $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
        } else {
            // 7bit/8bit encoding
            $encodedContent = $content;
            $headers .= "Content-Type: $contentType; charset=$charset\r\n";
            $headers .= "Content-Transfer-Encoding: $encoding\r\n";
        }
        
        // Chuyển đổi subject sang encoding đã chọn nếu cần
        if ($charset != 'UTF-8') {
            $encoded_subject = '';
            
            // Nếu charset là ISO-2022-JP, EUC-JP hoặc SJIS, sử dụng mb_encode_mimeheader
            if (in_array($charset, ['ISO-2022-JP', 'EUC-JP', 'SJIS'])) {
                $encoded_subject = mb_encode_mimeheader($subject, $charset, 'B');
            } 
            // Nếu không, sử dụng encoding B (base64) cho MIME header
            else {
                $encoded_subject = '=?' . $charset . '?B?' . base64_encode(mb_convert_encoding($subject, $charset, 'UTF-8')) . '?=';
            }
            
            $subject = $encoded_subject;
        }
        
        if ($smtp_config['enabled']) {
            // Sử dụng SMTP để gửi email
            $success = send_email_smtp($to, $subject, $encodedContent, $headers, $smtp_config);
        } else {
            // Sử dụng hàm mail() để gửi email
            $success = mail($to, $subject, $encodedContent, $headers);
        }
        
        if ($success) {
            $message = 'Email đã được gửi thành công!';
            $alertClass = 'alert-success';
            
            // Debug info
            $debug_info = "Headers:\n" . str_replace("\r\n", "<br>", $headers);
            $debug_info .= "<hr>Encoded Subject: $subject";
            $debug_info .= "<hr>First 100 chars of content: " . htmlspecialchars(substr($encodedContent, 0, 100)) . "...";
        } else {
            $message = 'Có lỗi xảy ra khi gửi email!';
            $alertClass = 'alert-danger';
            $debug_info = "Không thể gửi email. Vui lòng kiểm tra lại cấu hình máy chủ PHP.";
        }
    }
}

/**
 * Gửi email sử dụng SMTP - chức năng này yêu cầu thư viện bổ sung hoặc PHPMailer
 * Đây chỉ là phác thảo, cần thêm thư viện SMTP để thực hiện
 */
function send_email_smtp($to, $subject, $body, $headers, $config) {
    // Đây chỉ là mã giả để minh họa
    // Trong thực tế, bạn cần sử dụng thư viện như PHPMailer
    
    // Ví dụ với PHPMailer
    /*
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        $mail->setFrom($from);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML($use_html);
        $mail->CharSet = $charset;
        $mail->Encoding = $encoding;
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
    */
    
    // Trả về false vì chúng ta chưa triển khai thực tế
    return false;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Email với PHP - Nâng cao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f5f5f5;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Gửi Email</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="from" class="form-label">Từ (From):</label>
                                <input type="email" class="form-control" id="from" name="from" required 
                                    value="<?php echo isset($_POST['from']) ? htmlspecialchars($_POST['from']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="to" class="form-label">Đến (To):</label>
                                <input type="email" class="form-control" id="to" name="to" required
                                    value="<?php echo isset($_POST['to']) ? htmlspecialchars($_POST['to']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Chủ đề:</label>
                                <input type="text" class="form-control" id="subject" name="subject" required
                                    value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Nội dung:</label>
                                <textarea class="form-control" id="content" name="content" rows="8" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="charset" class="form-label">Bảng mã (Charset):</label>
                                    <select class="form-select" id="charset" name="charset">
                                        <option value="UTF-8" <?php echo (isset($_POST['charset']) && $_POST['charset'] == 'UTF-8') ? 'selected' : ''; ?>>UTF-8</option>
                                        <option value="ISO-2022-JP" <?php echo (isset($_POST['charset']) && $_POST['charset'] == 'ISO-2022-JP') ? 'selected' : ''; ?>>ISO-2022-JP</option>
                                        <option value="EUC-JP" <?php echo (isset($_POST['charset']) && $_POST['charset'] == 'EUC-JP') ? 'selected' : ''; ?>>EUC-JP</option>
                                        <option value="SJIS" <?php echo (isset($_POST['charset']) && $_POST['charset'] == 'SJIS') ? 'selected' : ''; ?>>SJIS (Shift-JIS)</option>
                                        <option value="ISO-8859-1" <?php echo (isset($_POST['charset']) && $_POST['charset'] == 'ISO-8859-1') ? 'selected' : ''; ?>>ISO-8859-1 (Latin-1)</option>
                                        <option value="ISO-8859-2" <?php echo (isset($_POST['charset']) && $_POST['charset'] == 'ISO-8859-2') ? 'selected' : ''; ?>>ISO-8859-2 (Latin-2)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="encoding" class="form-label">Mã hóa nội dung:</label>
                                    <select class="form-select" id="encoding" name="encoding">
                                        <option value="base64" <?php echo (isset($_POST['encoding']) && $_POST['encoding'] == 'base64') ? 'selected' : ''; ?>>Base64</option>
                                        <option value="quoted-printable" <?php echo (isset($_POST['encoding']) && $_POST['encoding'] == 'quoted-printable') ? 'selected' : ''; ?>>Quoted-printable</option>
                                        <option value="7bit" <?php echo (isset($_POST['encoding']) && $_POST['encoding'] == '7bit') ? 'selected' : ''; ?>>7bit</option>
                                        <option value="8bit" <?php echo (isset($_POST['encoding']) && $_POST['encoding'] == '8bit') ? 'selected' : ''; ?>>8bit</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="use_html" name="use_html" 
                                       <?php echo (isset($_POST['use_html'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="use_html">Sử dụng định dạng HTML</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Gửi Email</button>
                            </div>
                        </form>
                        
                        <?php if (!empty($debug_info)): ?>
                            <div class="debug-info mt-4">
                                <h5>Thông tin gỡ lỗi:</h5>
                                <hr>
                                <?php echo $debug_info; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>