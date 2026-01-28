<?php
/**
 * Terms & Conditions Modal
 * Usage: include this file and call showTermsModal($type)
 * Types: 'donation', 'volunteer', 'benefactor', 'event_creation'
 */

function showTermsModal($type, $targetForm = null) {
    global $pdo;
    
    $terms = getTermsByType($type);
    if (!$terms) {
        return '';
    }
    
    $modalId = 'termsModal' . ucfirst($type);
    $titles = [
        'donation' => 'Điều khoản Quyên góp',
        'volunteer' => 'Điều khoản Tình nguyện viên',
        'benefactor' => 'Điều khoản Nhà hảo tâm',
        'event_creation' => 'Điều khoản Tổ chức sự kiện'
    ];
    
    ob_start();
    ?>
    
    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-contract me-2"></i>
                        <?= $titles[$type] ?? 'Điều khoản' ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Lưu ý quan trọng:</strong> Vui lòng đọc kỹ các điều khoản dưới đây trước khi tiếp tục.
                    </div>
                    
                    <div class="terms-content">
                        <?= $terms['content'] ?>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="acceptTerms<?= ucfirst($type) ?>" required>
                        <label class="form-check-label fw-bold" for="acceptTerms<?= ucfirst($type) ?>">
                            <i class="fas fa-check-square text-success me-2"></i>
                            Tôi đã đọc, hiểu rõ và đồng ý với tất cả các điều khoản trên
                        </label>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="fas fa-info-circle me-2"></i>
                            Bằng việc tích chọn và nhấn "Đồng ý", bạn xác nhận rằng bạn đã đọc, 
                            hiểu và chấp thuận tuân thủ các điều khoản này.
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Hủy bỏ
                    </button>
                    <button type="button" class="btn btn-danger" id="btnAcceptTerms<?= ucfirst($type) ?>" disabled>
                        <i class="fas fa-check me-2"></i>Đồng ý và Tiếp tục
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    (function() {
        const checkbox = document.getElementById('acceptTerms<?= ucfirst($type) ?>');
        const acceptBtn = document.getElementById('btnAcceptTerms<?= ucfirst($type) ?>');
        const modal = document.getElementById('<?= $modalId ?>');
        
        // Enable button when checkbox is checked
        checkbox?.addEventListener('change', function() {
            acceptBtn.disabled = !this.checked;
        });
        
        // Handle accept button click
        acceptBtn?.addEventListener('click', function() {
            if (checkbox.checked) {
                // Hide modal
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance?.hide();
                
                // Set hidden field value
                const acceptedInput = document.getElementById('termsAccepted<?= ucfirst($type) ?>');
                if (acceptedInput) {
                    acceptedInput.value = '1';
                }
                
                // Submit target form if specified
                <?php if ($targetForm): ?>
                const targetForm = document.getElementById('<?= $targetForm ?>');
                if (targetForm) {
                    targetForm.submit();
                }
                <?php endif; ?>
                
                // Trigger custom event
                document.dispatchEvent(new CustomEvent('termsAccepted', {
                    detail: { type: '<?= $type ?>', termsId: <?= $terms['id'] ?> }
                }));
            }
        });
        
        // Reset checkbox when modal is closed
        modal?.addEventListener('hidden.bs.modal', function() {
            checkbox.checked = false;
            acceptBtn.disabled = true;
        });
    })();
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Show terms button - triggers modal
 */
function termsButton($type, $buttonText = null, $buttonClass = 'btn btn-danger') {
    $modalId = 'termsModal' . ucfirst($type);
    $defaultTexts = [
        'donation' => 'Quyên góp ngay',
        'volunteer' => 'Đăng ký tình nguyện',
        'benefactor' => 'Đăng ký nhà hảo tâm',
        'event_creation' => 'Tạo sự kiện'
    ];
    
    $text = $buttonText ?? $defaultTexts[$type] ?? 'Tiếp tục';
    
    return sprintf(
        '<button type="button" class="%s" data-bs-toggle="modal" data-bs-target="#%s">%s</button>',
        $buttonClass,
        $modalId,
        $text
    );
}

/**
 * Hidden field to store acceptance status
 */
function termsAcceptedField($type) {
    return sprintf(
        '<input type="hidden" name="terms_accepted" id="termsAccepted%s" value="0">',
        ucfirst($type)
    );
}
?>