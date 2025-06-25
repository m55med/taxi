<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="bg-gray-50 font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <header class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight">
                System Documentation
            </h1>
            <p class="mt-4 max-w-3xl mx-auto text-lg text-gray-500">
                Your one-stop reference for using, managing, and developing on the platform.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12">
            
            <!-- Sidebar Navigation -->
            <aside class="md:col-span-1">
                <div class="bg-white p-5 rounded-xl shadow-sm sticky top-24">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        Sections
                    </h2>
                    <nav id="doc-nav">
                        <ul class="space-y-1">
                            <li><a href="#user-guide" class="nav-link block py-2.5 px-4 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 font-medium transition-all duration-200">User Guide</a></li>
                            <li><a href="#staff-procedures" class="nav-link block py-2.5 px-4 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 font-medium transition-all duration-200">Staff Procedures</a></li>
                            <li><a href="#developer-docs" class="nav-link block py-2.5 px-4 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 font-medium transition-all duration-200">Developer Docs</a></li>
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="md:col-span-3 space-y-16">
                <!-- User Guide Section -->
                <section id="user-guide" class="scroll-mt-24">
                    <div class="bg-white p-8 rounded-xl shadow-sm transition-shadow duration-300 hover:shadow-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-4">User Guide</h2>
                        <div class="prose prose-lg max-w-none text-gray-600">
                            <p>Welcome to the User Guide. This section provides documentation on how to use the system's features from a user's perspective.</p>
                            <p>Here you can find detailed information about:</p>
                            <ul>
                                <li>Creating a new account and logging in.</li>
                                <li>Navigating the main dashboard.</li>
                                <li>How to manage trips and bookings.</li>
                                <li>Managing your profile and settings.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Staff Procedures Section -->
                <section id="staff-procedures" class="scroll-mt-24">
                     <div class="bg-white p-8 rounded-xl shadow-sm transition-shadow duration-300 hover:shadow-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-4">Staff Procedures</h2>
                        <div class="prose prose-lg max-w-none text-gray-600">
                            <p>This section outlines the standard operating procedures (SOPs) for staff members using the system.</p>
                            <p>Topics may include:</p>
                            <ul>
                                <li>Handling customer support tickets.</li>
                                <li>Company policies and internal procedures.</li>
                                <li>Accessing and interpreting performance reports.</li>
                                <li>Guidelines for using the ticketing and support system.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Developer Documentation Section -->
                <section id="developer-docs" class="scroll-mt-24">
                    <div class="bg-white p-8 rounded-xl shadow-sm transition-shadow duration-300 hover:shadow-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-4">Developer Docs</h2>
                        <div class="prose prose-lg max-w-none text-gray-600">
                            <p>Technical documentation for developers, including API information, database schema, and coding standards.</p>
                            <h3 class="font-semibold">Project Structure</h3>
                            <p>An overview of the main folders and files in the project.</p>
                            <h3 class="font-semibold">Database Schema</h3>
                            <p>A description of the main database tables and their relationships.</p>
                            <h3 class="font-semibold">API Endpoints</h3>
                            <p>A list of available API endpoints and how to use them.</p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('#doc-nav a');
    const sections = document.querySelectorAll('main section');

    // Smooth scroll for nav links
    navLinks.forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetElement = document.querySelector(this.getAttribute('href'));
            if(targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Highlight active nav link on scroll
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                navLinks.forEach(link => {
                    link.classList.remove('bg-blue-50', 'text-blue-600');
                    if (link.getAttribute('href') === `#${id}`) {
                        link.classList.add('bg-blue-50', 'text-blue-600');
                    }
                });
            }
        });
    }, { rootMargin: '-30% 0px -70% 0px' }); // Adjust margins to trigger when section is more central

    sections.forEach(section => {
        observer.observe(section);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 