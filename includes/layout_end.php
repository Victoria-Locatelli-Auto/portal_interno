            </main>

            <?php require __DIR__ . '/footer.php'; ?>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars($base_url); ?>/js/layout.js?v=<?php echo $asset_version('js/layout.js'); ?>"></script>

    <?php if (!empty($page_js)): ?>
        <script src="<?php echo htmlspecialchars($base_url . '/' . $page_js); ?>?v=<?php echo $asset_version($page_js); ?>"></script>
    <?php endif; ?>
</body>
</html>
