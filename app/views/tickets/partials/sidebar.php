<!-- Related Tickets -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-3 flex items-center">
        <i class="fas fa-history text-gray-400 mr-3"></i>
        Other Tickets by Customer
    </h2>
    <?php if (!empty($data['relatedTickets'])): ?>
         <ul class="space-y-3">
            <?php foreach ($data['relatedTickets'] as $relatedTicket): ?>
                 <li class="p-3 bg-gray-50 rounded-md border hover:bg-gray-100 transition">
                    <a href="<?= BASE_PATH . '/tickets/view/' . $relatedTicket['id'] ?>" class="font-semibold text-blue-600 hover:underline flex justify-between items-center">
                        <span>Ticket #<?= htmlspecialchars($relatedTicket['ticket_number']) ?></span>
                        <i class="fas fa-external-link-alt text-sm text-gray-400"></i>
                    </a>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="far fa-clock mr-1"></i>
                        <?= date('Y-m-d', strtotime($relatedTicket['created_at'])) ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
         <p class="text-gray-500 text-center py-4">No other tickets found for this customer.</p>
    <?php endif; ?>
</div> 