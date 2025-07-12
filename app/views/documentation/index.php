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
                            <li><a href="#reports" class="nav-link block py-2.5 px-4 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 font-medium transition-all duration-200">Reports</a></li>
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
                
                <!-- Reports Section -->
                <section id="reports" class="scroll-mt-24">
                    <div class="bg-white p-8 rounded-xl shadow-sm transition-shadow duration-300 hover:shadow-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-4">Reports</h2>
                        <div class="prose prose-lg max-w-none text-gray-600 space-y-8">
                            
                            <!-- Analytics Report -->
                            <div>
                                <h3 class="font-semibold text-xl text-gray-800">Analytics Dashboard</h3>
                                <p>Provides a high-level overview of system performance, including driver acquisition funnels, call center statistics, and ticketing trends. Ideal for managers and administrators to quickly gauge operational health.</p>
                                <a href="<?= BASE_PATH ?>/reports/analytics" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Analytics Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- System Logs Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">System Event Logs</h3>
                                <p>A detailed, filterable, and paginated report of events logged in the `discussions` table. Useful for developers and admins to trace system activities and debug issues. Supports filtering by event level, user, date range, and text search.</p>
                                <a href="<?= BASE_PATH ?>/reports/system-logs" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to System Logs <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Team Leaderboard Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Team Leaderboard</h3>
                                <p>Ranks teams based on a variety of performance metrics including points, call volume, and ticket counts. Features a chart for top team comparison and links to detailed user reports for each team.</p>
                                <a href="<?= BASE_PATH ?>/reports/team-leaderboard" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Team Leaderboard <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Employee Activity Score Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Employee Activity Score</h3>
                                <p>Ranks all employees based on their calculated activity points. This report is ideal for identifying top performers across the entire organization. Includes filters for team and role, and links to detailed activity logs for each user.</p>
                                <a href="<?= BASE_PATH ?>/reports/employee-activity-score" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Activity Score Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- My Activity Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">My Activity / User Activity</h3>
                                <p>A detailed dashboard showing the performance metrics and points breakdown for a single user. This is the page that "View Activity" links point to. Accessible by managers for their team members, and by employees to see their own stats.</p>
                                <a href="<?= BASE_PATH ?>/reports/myactivity" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to My Activity <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Drivers Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Drivers Report</h3>
                                <p>A master list of all drivers in the system. Includes advanced filtering by status, source, and date, along with charts visualizing the distribution of drivers. Links to detailed driver profiles.</p>
                                <a href="<?= BASE_PATH ?>/reports/drivers" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Drivers Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Driver Assignments Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Driver Assignments</h3>
                                <p>Provides a complete log of drivers being assigned from one user to another. Allows filtering by the driver, the user who assigned, and the user who received the assignment.</p>
                                <a href="<?= BASE_PATH ?>/reports/driver-assignments" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Driver Assignments <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Driver Calls Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Driver Calls</h3>
                                <p>A detailed log of all outbound calls made to drivers by system users. Supports filtering by driver, the user who made the call, their team, and the call status.</p>
                                <a href="<?= BASE_PATH ?>/reports/driver-calls" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Driver Calls Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Driver Documents Compliance Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Driver Documents Compliance</h3>
                                <p>Tracks the status of all required documents for all drivers. Allows filtering by driver, document type, and status (missing, submitted, rejected) to ensure compliance.</p>
                                <a href="<?= BASE_PATH ?>/reports/driver-documents-compliance" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Documents Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Tickets Summary Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Tickets Summary</h3>
                                <p>A dashboard of charts providing a high-level summary of ticket distribution by status (a proxy based on reviews), category, platform, and VIP status. Useful for quickly understanding ticketing trends.</p>
                                <a href="<?= BASE_PATH ?>/reports/tickets-summary" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Tickets Summary <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Detailed Tickets Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Detailed Tickets Log</h3>
                                <p>A comprehensive, paginated log of all individual tickets. This report provides deep filtering capabilities, allowing views by user, team, category, platform, and VIP status.</p>
                                <a href="<?= BASE_PATH ?>/reports/tickets" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Detailed Tickets Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Ticket Reviews Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Ticket Reviews</h3>
                                <p>A detailed log of all quality assurance reviews performed on tickets. Allows for filtering by the agent who created the ticket, the QA specialist who reviewed it, and the rating score.</p>
                                <a href="<?= BASE_PATH ?>/reports/ticket-reviews" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Ticket Reviews Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Ticket Discussions Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Ticket Discussions</h3>
                                <p>A log of all discussions initiated on tickets. Useful for quality control to review the reasons for disputes or re-evaluations. Filterable by user, status, and date.</p>
                                <a href="<?= BASE_PATH ?>/reports/ticket-discussions" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Discussions Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Ticket Coupons Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Ticket-Coupon Links</h3>
                                <p>Provides a log of every instance where a coupon was applied to a ticket. Filterable by the user who applied the coupon and searchable by ticket number or coupon code.</p>
                                <a href="<?= BASE_PATH ?>/reports/ticket-coupons" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Ticket Coupons Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

                            <!-- Trips Report -->
                            <div class="border-t pt-8">
                                <h3 class="font-semibold text-xl text-gray-800">Trips Report & Dashboard</h3>
                                <p>A comprehensive two-part report. It features a high-level KPI dashboard for monitoring overall trip statistics and identifying suspicious driver/passenger activity, as well as a detailed, paginated log of every trip in the system.</p>
                                <a href="<?= BASE_PATH ?>/reports/trips" class="inline-block mt-3 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition text-base no-underline">
                                    Go to Trips Report <span class="ml-2">&rarr;</span>
                                </a>
                            </div>

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