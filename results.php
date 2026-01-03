<?php
/**
 * Public Results Page
 */
require_once 'includes/config.php';
$pageTitle = 'Results';
require_once 'components/navbar.php';

try {
    // Recent finished races with winner
    $stmt = $db->prepare(" 
        SELECT r.id, r.name, r.race_date, r.race_time, t.name as track_name,
               (SELECT h.name FROM race_results rr 
                JOIN race_entries re ON rr.race_entry_id = re.id 
                JOIN horses h ON re.horse_id = h.id 
                WHERE rr.race_id = r.id AND rr.finish_position = 1 LIMIT 1) as winner
        FROM races r
        LEFT JOIN tracks t ON r.track_id = t.id
        WHERE r.status = 'finished'
        AND EXISTS (SELECT 1 FROM race_results WHERE race_id = r.id)
        ORDER BY r.race_date DESC, r.race_time DESC
        LIMIT 30
    ");
    $stmt->execute();
    $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $races = [];
}
?>

<section class="bg-white py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-6">Recent Results</h1>

        <?php if (empty($races)): ?>
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <p class="text-gray-600">No results available yet.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($races as $race): ?>
                    <div class="bg-white shadow rounded-lg p-4 border-l-4 border-emerald-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold"><?php echo htmlspecialchars($race['name']); ?></h2>
                                <div class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($race['track_name']); ?> • <?php echo formatDate($race['race_date']); ?> <?php echo formatTime($race['race_time']); ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Winner</div>
                                <div class="text-lg font-bold text-emerald-700"><?php echo htmlspecialchars($race['winner'] ?? 'TBD'); ?></div>
                                <a href="racecard.php?id=<?php echo $race['id']; ?>" class="inline-block mt-2 text-sm text-emerald-600 hover:underline">View full results</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'components/footer.php'; ?>
