<?php
// footer.php - Complete footer without session_start()
?>

        <!-- Main content ends here -->
    </div> <!-- .content-wrapper ends -->
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <!-- Footer Top -->
            <div class="footer-top">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-graduation-cap"></i>
                        <span>ShikshaHub</span>
                    </div>
                    <p class="footer-description">
                       Empowering education through sharing knowledge.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                       
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="materials.php"><i class="fas fa-book"></i> Study Materials</a></li>
                        <li><a href="upload.php"><i class="fas fa-upload"></i> Upload Files</a></li>
                        
                        
                    </ul>
                </div>
                
            
                
                <div class="footer-section">
                    <h3 class="footer-title">Contact Info</h3>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Shiksha city</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+977 9841434343</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>support@shikshahub.com</span>
                        </li>
                      
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> ShikshaHub. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- Footer CSS -->
    <style>
        /* ====== FOOTER STYLES ====== */
        .site-footer {
            background: linear-gradient(135deg, var(--secondary) 0%, #1a252f 100%);
            color: white;
            padding: 60px 0 20px;
            margin-top: 50px;
        }
        
        .footer-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Footer Top */
        .footer-top {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section {
            padding: 0 15px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .footer-logo i {
            font-size: 32px;
        }
        
        .footer-description {
            color: #bdc3c7;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-title {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(52, 152, 219, 0.3);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .footer-links a i {
            width: 20px;
        }
        
        .contact-info {
            list-style: none;
        }
        
        .contact-info li {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        
        .contact-info i {
            color: var(--primary);
            margin-top: 3px;
        }
        
        /* Footer Bottom */
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .copyright {
            color: #bdc3c7;
        }
        
        .footer-stats {
            display: flex;
            gap: 30px;
        }
        
        .footer-stats span {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #bdc3c7;
        }
        
        .footer-stats i {
            color: var(--primary);
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .back-to-top:hover {
            background: var(--secondary);
            transform: translateY(-3px);
        }
        
        .back-to-top.show {
            display: flex;
            animation: fadeInUp 0.3s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Footer */
        @media (max-width: 992px) {
            .footer-top {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            .footer-top {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .footer-section {
                padding: 0;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .footer-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }
    </style>
    
    <!-- Footer JavaScript -->
    <script>
        // Back to Top Button
        const backToTop = document.getElementById('backToTop');
        
        if (backToTop) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });
            
            backToTop.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Update copyright year
        document.addEventListener('DOMContentLoaded', function() {
            const yearElements = document.querySelectorAll('.copyright p');
            yearElements.forEach(function(element) {
                element.innerHTML = element.innerHTML.replace('<?php echo date("Y"); ?>', new Date().getFullYear());
            });
        });
    </script>
    
    <!-- Page-Specific JavaScript -->
    <?php if (isset($page_js)): ?>
        <script>
            <?php echo $page_js; ?>
        </script>
    <?php endif; ?>
    
</body>
</html>