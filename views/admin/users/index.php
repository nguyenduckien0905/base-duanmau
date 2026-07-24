<!-- Bộ lọc tài khoản. -->
<div class="toolbar">
    <form class="filter-form" action="<?= e(url('admin/users')) ?>" method="get">
        <input type="hidden" name="action" value="admin/users">

        <input
            type="search"
            name="keyword"
            value="<?= e($keyword) ?>"
            placeholder="Tên, email, số điện thoại..."
        >

        <select name="role_id">
            <option value="">Tất cả vai trò</option>
            <option value="1" <?= $roleId === 1 ? 'selected' : '' ?>>Admin</option>
            <option value="2" <?= $roleId === 2 ? 'selected' : '' ?>>Staff</option>
            <option value="3" <?= $roleId === 3 ? 'selected' : '' ?>>Member</option>
        </select>

        <button class="btn btn-light" type="submit">Lọc</button>
    </form>
</div>

<!-- Bảng tài khoản. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Liên hệ</th>
                    <th>Vai trò</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?= e($user['user_id']) ?></td>
                        <td>
                            <strong><?= e($user['fullname']) ?></strong>
                            <?php if ((int) $user['user_id'] === (int) (currentAdmin()['user_id'] ?? 0)): ?>
                                <small class="table-subtext">Tài khoản của bạn</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= e($user['email']) ?>
                            <small class="table-subtext"><?= e($user['phone'] ?: 'Chưa có SĐT') ?></small>
                        </td>
                        <td><span class="badge badge-info"><?= e($user['role_name']) ?></span></td>
                        <td><?= e(date('d/m/Y', strtotime($user['created_at']))) ?></td>
                        <td>
                            <span class="badge <?= (int) $user['status'] === 1 ? 'badge-success' : 'badge-danger' ?>">
                                <?= (int) $user['status'] === 1 ? 'Hoạt động' : 'Bị khóa' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <?php if ((int) $user['user_id'] !== (int) (currentAdmin()['user_id'] ?? 0)): ?>
                                <form
                                    action="<?= e(url('admin/users/toggle-status', ['id' => $user['user_id']])) ?>"
                                    method="post"
                                    data-confirm="Bạn có chắc muốn đổi trạng thái tài khoản này?"
                                >
                                    <?= csrfField() ?>
                                    <button
                                        class="btn <?= (int) $user['status'] === 1 ? 'btn-danger' : 'btn-light' ?> btn-small"
                                        type="submit"
                                    >
                                        <?= (int) $user['status'] === 1 ? 'Khóa' : 'Mở khóa' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Không tìm thấy tài khoản.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
