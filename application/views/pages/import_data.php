<?php extend('layouts/backend_layout'); ?>

<?php section('content'); ?>

<div class="container backend-page" id="data-import-page">
    <div class="row" id="data-import">
        <div id="import-systems" class="import-systems filter-records col col-12 col-lg-5">
            <h4 class="text-black-50 mb-3 fw-light">
                <?= lang('data_import') ?> 
            </h4>

            <h5 class="text-black-50 mb-3 fw-light">
                <?= lang('source_systems') ?>
            </h5>

            <div class="systems">
                <!-- JS -->
            </div>
        </div>
        
        <div class="import-system-details column col-12 col-lg-5">
                
            <div class="d-flex justify-left align-items-center border-bottom mb-4 py-2">
                <h4 class="import-data-title text-black-50 mb-0 fw-light">
                    <?= lang('import_data') ?>
                </h4>
            </div>

            <form>
                <fieldset>
                    <div class="datasets">
                        <!-- JS -->
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<?php end_section('content'); ?>

<?php section('scripts'); ?>

<script src="<?= asset_url('assets/js/http/import_data_http_client.js') ?>"></script>
<script src="<?= asset_url('assets/js/pages/import_data.js') ?>"></script>

<?php end_section('scripts'); ?>
