<?php
if (!empty($activitiesSummary)) {
    // Calculate totals and get individual counts
    $normal_tickets = $activitiesSummary['Normal Ticket'] ?? 0;
    $vip_tickets = $activitiesSummary['VIP Ticket'] ?? 0;
    $total_tickets = $normal_tickets + $vip_tickets;

    $incoming_calls = $activitiesSummary['Incoming Call'] ?? 0;
    $outgoing_calls = $activitiesSummary['Outgoing Call'] ?? 0;
    $total_calls = $incoming_calls + $outgoing_calls;

    $assignments = $activitiesSummary['Assignment'] ?? 0;
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-6">
    
    <!-- Tickets Summary Card -->
    <?php if ($total_tickets > 0): ?>
    <div class="bg-blue-600 text-white p-4 rounded-lg shadow-lg flex flex-col justify-between h-full">
        <div>
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Tickets</h3>
                <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
            </div>
            <p class="text-4xl font-bold mt-2"><?= $total_tickets ?></p>
        </div>
        <div class="border-t border-blue-500 mt-4 pt-2 text-sm flex justify-between">
            <span><i class="fas fa-ticket-alt text-blue-300 mr-1"></i> Normal: <strong class="font-semibold"><?= $normal_tickets ?></strong></span>
            <span><i class="fas fa-crown text-yellow-300 mr-1"></i> VIP: <strong class="font-semibold"><?= $vip_tickets ?></strong></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Calls Summary Card -->
    <?php if ($total_calls > 0): ?>
    <div class="bg-green-600 text-white p-4 rounded-lg shadow-lg flex flex-col justify-between h-full">
        <div>
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Calls</h3>
                <i class="fas fa-phone-volume fa-2x opacity-50"></i>
            </div>
            <p class="text-4xl font-bold mt-2"><?= $total_calls ?></p>
        </div>
        <div class="border-t border-green-500 mt-4 pt-2 text-sm flex justify-between">
            <span><i class="fas fa-arrow-down text-green-300 mr-1"></i> Incoming: <strong class="font-semibold"><?= $incoming_calls ?></strong></span>
            <span><i class="fas fa-arrow-up text-green-300 mr-1"></i> Outgoing: <strong class="font-semibold"><?= $outgoing_calls ?></strong></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assignments Summary Card -->
    <?php if ($assignments > 0): ?>
    <div class="bg-purple-600 text-white p-4 rounded-lg shadow-lg flex flex-col justify-between h-full">
        <div>
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Assignments</h3>
                <i class="fas fa-user-tag fa-2x opacity-50"></i>
            </div>
            <p class="text-4xl font-bold mt-2"><?= $assignments ?></p>
        </div>
        <div class="border-t border-purple-500 mt-4 pt-2 text-sm invisible">
            <span>&nbsp;</span>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php 
}
?> 