<?php
require_once __DIR__ . '/../config/session.php';
?>
<style>
/* --- Modern Navbar Styling --- */
.navbar {
    background-color: #ffffff !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
    padding: 0.8rem 0;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.navbar.scrolled {
    padding: 0.5rem 0;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.6rem;
    color: #0066cc !important;
    letter-spacing: -0.5px;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
}

.navbar-brand i {
    font-size: 1.4rem;
}

.navbar-brand span {
    background: linear-gradient(135deg, #0066cc, #004d99);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-link {
    color: #2d3748 !important;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border-radius: 8px;
    padding: 0.5rem 1rem !important;
    margin: 0 0.1rem;
    position: relative;
    font-family: 'Inter', sans-serif;
}

.nav-link:hover {
    color: #0066cc !important;
    background-color: rgba(0, 102, 204, 0.05);
    transform: translateY(-1px);
}

.nav-link.active {
    color: #0066cc !important;
    background-color: rgba(0, 102, 204, 0.08);
    font-weight: 600;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #0066cc, #004d99);
    transition: all 0.3s ease;
    transform: translateX(-50%);
    border-radius: 2px;
}

.nav-link:hover::after,
.nav-link.active::after {
    width: 80%;
}

/* Special styling for auth links */
.navbar-nav.ms-auto .nav-link {
    border-radius: 6px;
    margin-left: 0.3rem;
}

.navbar-nav.ms-auto .nav-link:last-child {
    background: linear-gradient(135deg, #0066cc, #004d99);
    color: white !important;
    padding: 0.5rem 1.2rem !important;
    margin-left: 0.5rem;
    box-shadow: 0 2px 10px rgba(0, 102, 204, 0.2);
}

.navbar-nav.ms-auto .nav-link:last-child:hover {
    background: linear-gradient(135deg, #0052a3, #003d7a);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
    color: white !important;
}

/* Dashboard/Admin links styling */
.navbar-nav.ms-auto .nav-link[href*="dashboard"] {
    background: rgba(0, 102, 204, 0.1);
    color: #0066cc !important;
    font-weight: 600;
}

.navbar-nav.ms-auto .nav-link[href*="dashboard"]:hover {
    background: rgba(0, 102, 204, 0.15);
    color: #004d99 !important;
}

/* Logout link styling */
.navbar-nav.ms-auto .nav-link[href="logout.php"] {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626 !important;
}

.navbar-nav.ms-auto .nav-link[href="logout.php"]:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #b91c1c !important;
}

.navbar-toggler {
    border: none;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.2);
    outline: none;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%280, 102, 204, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    width: 1.2em;
    height: 1.2em;
}

/* Mobile responsive styles */
@media (max-width: 991px) {
    .navbar-collapse {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .navbar-nav .nav-item {
        margin: 0.2rem 0;
    }
    
    .navbar-nav.ms-auto {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding-top: 1rem;
        margin-top: 0.5rem;
    }
    
    .nav-link::after {
        display: none;
    }
    
    .navbar-nav.ms-auto .nav-link:last-child {
        margin-left: 0;
        margin-top: 0.5rem;
        text-align: center;
    }
}

/* Active page detection */
.nav-link[href="<?php echo basename($_SERVER['PHP_SELF']); ?>"] {
    color: #0066cc !important;
    background-color: rgba(0, 102, 204, 0.08);
    font-weight: 600;
}

/* Animation for navbar items */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.navbar-nav .nav-item {
    animation: fadeInDown 0.5s ease forwards;
    opacity: 0;
}

.navbar-nav .nav-item:nth-child(1) { animation-delay: 0.1s; }
.navbar-nav .nav-item:nth-child(2) { animation-delay: 0.2s; }
.navbar-nav .nav-item:nth-child(3) { animation-delay: 0.3s; }
.navbar-nav .nav-item:nth-child(4) { animation-delay: 0.4s; }
.navbar-nav.ms-auto .nav-item:nth-child(1) { animation-delay: 0.5s; }
.navbar-nav.ms-auto .nav-item:nth-child(2) { animation-delay: 0.6s; }
.navbar-nav.ms-auto .nav-item:nth-child(3) { animation-delay: 0.7s; }
</style>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-car"></i>
            <span>CarEase</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : ''; ?>" href="cars.php">Our Cars</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a href="admin/dashboard.php" class="nav-link">
                                <i class="fas fa-tachometer-alt me-1"></i>Admin Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="fas fa-user-circle me-1"></i>Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/login.php" class="nav-link">
                            <i class="fas fa-lock me-1"></i>Admin Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
// Add scroll effect to navbar
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Add active class based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || 
            (currentPage === '' && linkPage === 'index.php') ||
            (linkPage.includes(currentPage) && currentPage !== '')) {
            link.classList.add('active');
        }
    });
});
</script>