<?php /** static/contact.php */ ?>
<div class="static-page">
    <div class="container">
        <div class="static-hero">
            <h1 class="static-hero__title">Liên hệ với chúng tôi</h1>
            <p class="static-hero__lead">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn.</p>
        </div>

        <div class="contact-layout">
            <div class="contact-info">
                <h2>Thông tin liên hệ</h2>
                <ul class="contact-list">
                    <li><span class="contact-icon">📍</span> 123 Đường Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh</li>
                    <li><span class="contact-icon">📞</span> <a href="tel:19001234">1900 1234</a> (8:00 - 22:00)</li>
                    <li><span class="contact-icon">✉️</span> <a href="mailto:support@sportzone.vn">support@sportzone.vn</a></li>
                    <li><span class="contact-icon">⏰</span> Thứ 2 - Chủ nhật: 8:00 - 22:00</li>
                </ul>
            </div>

            <form class="contact-form" id="contact-form" novalidate>
                <h2>Gửi tin nhắn</h2>
                <div class="form-group">
                    <label for="contact-name" class="form-label">Họ tên</label>
                    <input type="text" id="contact-name" class="form-control" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="form-group">
                    <label for="contact-email" class="form-label">Email</label>
                    <input type="email" id="contact-email" class="form-control" placeholder="ban@email.com" required>
                </div>
                <div class="form-group">
                    <label for="contact-msg" class="form-label">Nội dung</label>
                    <textarea id="contact-msg" class="form-control" rows="5" placeholder="Nhập nội dung cần hỗ trợ..." required></textarea>
                </div>
                <button type="submit" class="btn btn--primary" id="contact-submit">Gửi tin nhắn</button>
            </form>
        </div>
    </div>
</div>