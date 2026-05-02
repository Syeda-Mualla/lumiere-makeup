    </main>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3 class="footer-logo">LUMIÈRE</h3>
                    <p>Where Beauty Meets Elegance. Discover luxury cosmetics crafted for the modern woman.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo isset($isAdmin) ? '../shop.php' : 'shop.php'; ?>">Shop All</a></li>
                        <li><a href="<?php echo isset($isAdmin) ? '../blog.php' : 'blog.php'; ?>">Beauty Blog</a></li>
                        <li><a href="<?php echo isset($isAdmin) ? '../community.php' : 'community.php'; ?>">Glam Feed</a></li>
                        <li><a href="#">About Us</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Customer Care</h4>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Stay Connected</h4>
                    <p>Subscribe for exclusive offers and beauty tips.</p>
                    <form class="footer-newsletter" id="footerNewsletter">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit"><i class="fas fa-arrow-right"></i></button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> LUMIÈRE. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="<?php echo isset($isAdmin) ? '../js/main.js' : 'js/main.js'; ?>"></script>
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</body>
</html>
