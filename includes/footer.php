            </div>
            <footer class="main-footer">
                Desenvolvido por <a href="https://wa.me/+5512992106218" target="_blank">Thomas Marcelino</a>
                <span class="version-tag"><?php echo defined('SYSTEM_VERSION') ? SYSTEM_VERSION : 'null'; ?></span>
            </footer>
        </main>
    </div>

    <!-- Global Modal Structure -->
    <div id="global-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Título do Modal</h3>
                <button class="close-modal" onclick="UI.closeModal()">&times;</button>
            </div>
            <div id="modal-body">
                <!-- Content will be injected here -->
            </div>
            <div id="modal-footer" class="modal-footer" style="display: none;"></div>
        </div>
    </div>

    <!-- Global Toast Container -->
    <div id="toast-container" class="toast-container"></div>



    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- SaaSFlow Core Components -->
    <script src="<?php echo \App\Core\Controller::asset('/assets/js/components/ui-core.js'); ?>"></script>
    <script src="<?php echo \App\Core\Controller::asset('/assets/js/components/ajax-handler.js'); ?>"></script>
    <script src="<?php echo \App\Core\Controller::asset('/assets/js/components/input-masks.js'); ?>"></script>
    <script src="<?php echo \App\Core\Controller::asset('/assets/js/components/utils.js'); ?>"></script>
    <script src="<?php echo \App\Core\Controller::asset('/assets/js/main.js'); ?>"></script>
    
    <?php 
    // Load module specific JS
    // A variável $current_page vem do header.php incluído no mesmo escopo local do Controller->render()
    $page_for_js = $current_page ?? 'dashboard';
    $module_js_path = '/assets/js/modules/' . str_replace('.php', '.js', $page_for_js);
    $real_js_path = __DIR__ . '/../public' . $module_js_path;
    
    if (file_exists($real_js_path)): ?>
        <script src="<?php echo \App\Core\Controller::asset($module_js_path); ?>"></script>
    <?php endif; ?>
</body>
</html>
