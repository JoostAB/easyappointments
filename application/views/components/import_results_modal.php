<?php
/**
 * Local variables.
 * 
 * @var array $import_result
 */
?>

<div id="import-results-modal" class="modal fade import-results-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= lang('import_results_title') ?></h3>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="import-results-content">
                    <div class="import-result-item">
                        <h6></h6>
                        <div class="card import-result-item-lines">
                            <div class="card-body overflow-auto import-result-data"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= lang('close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php section('scripts'); ?>

<script src="<?= asset_url('assets/js/components/import_results_modal.js') ?>"></script>

<?php end_section('scripts'); ?>

