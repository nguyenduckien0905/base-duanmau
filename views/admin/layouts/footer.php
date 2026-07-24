    </div>

    <!-- Script hỏi lại trước khi gửi form xóa dữ liệu. -->
    <script>
        // Tìm toàn bộ form có thuộc tính data-confirm.
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            // Lắng nghe sự kiện submit của từng form.
            form.addEventListener('submit', function (event) {
                // Không gửi form nếu người dùng bấm Hủy.
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
