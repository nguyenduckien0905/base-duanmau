<!-- Chỉ hiện khối lỗi khi controller gửi sang ít nhất một lỗi. -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
