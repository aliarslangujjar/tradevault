    </main>
    
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                TradeVault v<?php echo APP_VERSION; ?> &copy; <?php echo date('Y'); ?> 
                | <a href="#" data-bs-toggle="modal" data-bs-target="#aboutModal">About</a>
            </span>
        </div>
    </footer>

    <!-- About Modal -->
    <div class="modal fade" id="aboutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">About TradeVault</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>A trading journal to track your trades, analyze performance, and improve your strategy.</p>
                    <p><strong>Version:</strong> <?php echo APP_VERSION; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <?php if (function_exists('custom_scripts')) { custom_scripts(); } ?>
</body>
</html>