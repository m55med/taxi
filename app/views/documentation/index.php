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
                        <div class="prose prose-lg max-w-none text-gray-600 space-y-8">
                            <div>
                                <h3 class="font-semibold text-xl text-gray-800">New Routing System (July 2025 Refactor)</h3>
                                <p>The application has been updated to use a new, more robust routing system. All route definitions have been moved from the legacy <code>App.php</code> file to a centralized routing file.</p>
                                <ul>
                                    <li><strong>Core Router:</strong> The main logic is handled by <code>app/core/Router.php</code>.</li>
                                    <li><strong>Route Definitions:</strong> All web routes must be defined in <code>app/routes/web.php</code>.</li>
                                </ul>
                                <p>To define a route, use the <code>$router</code> variable available in the file:</p>
                                <pre><code class="language-php">// To handle a GET request:
$router->get('your/uri', 'ControllerFolder/ControllerName@methodName');

// To handle a POST request:
$router->post('your/uri', 'ControllerFolder/ControllerName@methodName');

// Example with dynamic parameters:
$router->get('users/{id}', 'Users/UsersController@show');
</code></pre>
                            </div>

                            <div>
                                <h3 class="font-semibold text-xl text-gray-800">Server Configuration</h3>
                                <p>For the new routing system to work correctly, the web server must redirect all non-file requests to the main entry point of the application.</p>
                                <ul>
                                    <li><strong>Apache:</strong> Ensure <code>mod_rewrite</code> is enabled in your Apache configuration. The root <code>.htaccess</code> file handles the redirection logic. You might also need to set <code>AllowOverride All</code> for the project directory in your <code>httpd.conf</code> file.</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="font-semibold text-xl text-gray-800">Composer Commands</h3>
                                <p>When you create a new class file (like a new Controller or Model), you must update Composer's autoloader map. Otherwise, the application will not be able to find your class.</p>
                                <p>Run the following command from the project root:</p>
                                <pre><code class="language-bash">composer dump-autoload</code></pre>
                            </div>

                            <div>
                                <h3 class="font-semibold text-xl text-gray-800">Permissions System: Batch Updates</h3>
                                <p>To improve performance on the Admin Permissions page, a batch update system was implemented. This avoids sending one API request for every single permission change when using the "Toggle All" or group toggle switches.</p>
                                
                                <h4 class="font-semibold text-lg text-gray-800 mt-4">The Problem</h4>
                                <p>The previous implementation triggered an individual `fetch` request for each checkbox changed by a master toggle. For a role with 50+ permissions, this meant 50+ separate HTTP requests and database transactions, causing significant UI lag and high server load.</p>

                                <h4 class="font-semibold text-lg text-gray-800 mt-4">The Solution: Batch Processing</h4>
                                <p>The new system gathers all affected permission IDs on the frontend and sends them in a single request to a new, dedicated backend endpoint. The backend then processes all these changes in a single, efficient database transaction.</p>

                                <h5 class="font-semibold text-md text-gray-800 mt-4">Backend Implementation</h5>
                                <ul>
                                    <li><strong>Controller:</strong> `app/controllers/admin/PermissionsController.php` now has two new methods:
                                        <ul class="list-disc ml-6">
                                            <li>`batchUpdateRolePermissions()`</li>
                                            <li>`batchUpdateUserPermissions()`</li>
                                        </ul>
                                        These methods expect a JSON payload with a list of permission IDs and a grant status.
                                    </li>
                                    <li><strong>Model:</strong> `app/models/admin/Permission.php` contains the core logic in:
                                        <ul class="list-disc ml-6">
                                            <li>`syncRolePermissions()`</li>
                                            <li>`syncUserPermissions()`</li>
                                        </ul>
                                        These methods use efficient SQL (`INSERT IGNORE` for adding multiple rows, and `DELETE ... WHERE IN (...)` for removing multiple rows) to perform the update in one query.
                                    </li>
                                </ul>

                                <h5 class="font-semibold text-md text-gray-800 mt-4">API Endpoints</h5>
                                <p>The following `POST` routes were added to `app/routes/web.php`:</p>
                                <pre><code class="language-php">$router->post("admin/permissions/batchUpdateRolePermissions", ...);
$router->post("admin/permissions/batchUpdateUserPermissions", ...);
</code></pre>
                                <p>The expected JSON payload for these endpoints is:</p>
                                <pre><code class="language-json">{
    "role_id": 123, // or "user_id": 456
    "permission_ids": [1, 2, 3, 5, 8],
    "grant": true // or false
}</code></pre>

                                <h5 class="font-semibold text-md text-gray-800 mt-4">Frontend Implementation</h5>
                                 <p>The JavaScript in `app/views/admin/permissions/index.php` was refactored. The event listeners for the group and master toggles now call a new `handleBatchUpdate` function, which constructs the JSON payload and sends the single `fetch` request.</p>
                            </div>

                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Project Structure (Legacy)</h3>
                                <p>An overview of the main folders and files in the project.</p>
                                <h3 class="font-semibold mt-4 text-xl text-gray-800">Database Schema (Legacy)</h3>
                                <p>A description of the main database tables and their relationships.</p>
                                <h3 class="font-semibold mt-4 text-xl text-gray-800">API Endpoints (Legacy)</h3>
                                <p>A list of available API endpoints and how to use them.</p>
                            </div>
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