<?php
$params = $_GET;
?>
<nav>
    <ul class="pagination pagination-sm mb-0 justify-content-center">
        <?php if ($current_page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?= http_build_query(array_merge($params, ['page' => $current_page - 1])) ?>">«</a>
            </li>
        <?php endif ?>
        <?php
        $start = max(1, $current_page - 2);
        $end   = min($last_page, $current_page + 2);
        for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($params, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor ?>
        <?php if ($current_page < $last_page): ?>
            <li class="page-item">
                <a class="page-link" href="?<?= http_build_query(array_merge($params, ['page' => $current_page + 1])) ?>">»</a>
            </li>
        <?php endif ?>
    </ul>
</nav>
