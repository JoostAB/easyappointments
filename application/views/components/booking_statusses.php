<div class="">
    <ul class="list-group booking_statusses_list">
        <li class="d-flex justify-content-between align-items-center p-0 border-0 mb-3 booking-status">
            <div class="w-25 me-2">
                <span><?= lang('name') ?></span>
            </div>
            <div class="w-75 me-2">
                <span><?= lang('description') ?></span>
            </div>
             <div class="me-2">
                <span><?= lang('occupied') ?></span>
            </div>
            <button type="button" class="btn invisible" disabled>
                <i class="fas fa-trash"></i>
            </button>
        </li>
        <!-- JS -->
    </ul>

    <button type="button" class="btn btn-outline-primary btn-sm add-booking-status">
        <i class="fas fa-plus-square me-2"></i>
        <?= lang('add') ?>
    </button>
    <button type="button" class="btn btn-outline-secondary btn-sm load-booking-status">
        <i class="fas fa-arrow-rotate-right me-2"></i>
        <?= lang('reset') ?>
    </button>
</div>

<?php section('scripts'); ?>

<script src="<?= asset_url('assets/js/components/booking_statusses.js') ?>"></script>

<?php end_section('scripts'); ?>