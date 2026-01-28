<footer class="footer bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">
            <!-- Column 1: About -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="text-danger mb-3">
                    <i class="fas fa-heart me-2"></i>CHARITY EVENT
                </h5>
                <p class="text-white-50">
                    Nền tảng kết nối từ thiện minh bạch, kết nối những tấm lòng nhân ái 
                    để mang yêu thương đến mọi miền đất nước.
                </p>
                <div class="social-links mt-3">
                    <a href="#" class="btn btn-outline-light btn-sm me-2">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm me-2">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm me-2">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
                
                <?php
                $stats = getStatistics();
                if ($stats):
                ?>
                <div class="stats-footer mt-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat-item text-center p-2 bg-danger bg-opacity-10 rounded">
                                <div class="fw-bold text-warning"><?= $stats['total_events'] ?>+</div>
                                <small class="text-white-50">Sự kiện</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item text-center p-2 bg-danger bg-opacity-10 rounded">
                                <div class="fw-bold text-warning"><?= $stats['total_donors'] ?>+</div>
                                <small class="text-white-50">Nhà hảo tâm</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Column 2: Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3">Liên kết</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/about.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Về chúng tôi
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/events.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Sự kiện
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/news.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Tin tức
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/benefactor/apply.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Tổ chức sự kiện
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/contact.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Liên hệ
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 3: Support -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3">Hỗ trợ</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/faq.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Câu hỏi thường gặp
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/how-it-works.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Hướng dẫn sử dụng
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/privacy.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Chính sách bảo mật
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/terms.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Điều khoản sử dụng
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/transparency.php" class="text-white-50 text-decoration-none hover-text-white">
                            <i class="fas fa-chevron-right me-2 small"></i>Minh bạch tài chính
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 4: Contact -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase mb-3">Liên hệ</h6>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                        Hà Nội, Việt Nam
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2 text-danger"></i>
                        <a href="tel:19001234" class="text-white-50 text-decoration-none">1900 1234</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2 text-danger"></i>
                        <a href="mailto:contact@charityevent.vn" class="text-white-50 text-decoration-none">
                            contact@charityevent.vn
                        </a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock me-2 text-danger"></i>
                        24/7 - Luôn sẵn sàng hỗ trợ
                    </li>
                </ul>
                
                <!-- Newsletter Signup -->
                <div class="newsletter-signup mt-3">
                    <h6 class="text-uppercase mb-2 small">Nhận tin mới nhất</h6>
                    <form action="<?= BASE_URL ?>/subscribe.php" method="POST" class="input-group input-group-sm">
                        <input type="email" name="email" class="form-control" placeholder="Email của bạn" required>
                        <button class="btn btn-danger" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <hr class="bg-secondary my-4">
        
        <!-- Bottom Footer -->
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0 text-white-50 small">
                    &copy; <?= date('Y') ?> <strong>Charity Event</strong>. All rights reserved. 
                    Made with <i class="fas fa-heart text-danger"></i> in Vietnam
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="payment-methods">
                    <small class="text-white-50 me-3">Phương thức thanh toán:</small>
                    <img src="https://img.icons8.com/color/48/visa.png" alt="Visa" style="height: 24px;" class="me-2">
                    <img src="https://img.icons8.com/color/48/mastercard.png" alt="Mastercard" style="height: 24px;" class="me-2">
                    <img src="https://img.icons8.com/color/48/momo.png" alt="MoMo" style="height: 24px;" class="me-2">
                    <img src="https://img.icons8.com/color/48/bank-card-back-side.png" alt="Banking" style="height: 24px;">
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-danger btn-floating" style="display: none;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>/public/js/main.js"></script>

<!-- Additional JS for specific pages -->
<?php if (isset($additionalJS)): ?>
    <?= $additionalJS ?>
<?php endif; ?>

<script>
// Back to top button
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTop.style.display = 'block';
    } else {
        backToTop.style.display = 'none';
    }
});

document.getElementById('backToTop')?.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

</body>
</html>