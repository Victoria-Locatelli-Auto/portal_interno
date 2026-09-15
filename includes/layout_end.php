            </main>

            <?php require __DIR__ . '/footer.php'; ?>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars($base_url); ?>/js/layout.js"></script>

    <?php if (!empty($page_js)): ?>
        <script src="<?php echo htmlspecialchars($base_url . '/' . $page_js); ?>"></script>
    <?php endif; ?>
</body>
</html>
