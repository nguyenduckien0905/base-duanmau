<?php

/**
 * Controller nhận đánh giá sản phẩm từ khách đã hoàn thành đơn hàng.
 */
class ClientReviewController
{
    private ClientReviewModel $reviewModel;
    private ClientAuthModel $authModel;

    public function __construct()
    {
        $this->reviewModel = new ClientReviewModel();
        $this->authModel = new ClientAuthModel();
    }

    /**
     * Tạo đánh giá bằng POST từ trang chi tiết đơn hàng.
     */
    public function create(): void
    {
        requireClient();
        $this->ensureActiveClient();

        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        $orderId = max(0, (int) ($_POST['order_id'] ?? 0));
        $orderItemId = max(0, (int) ($_POST['order_item_id'] ?? 0));
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim((string) ($_POST['comment'] ?? ''));

        if ($rating < 1 || $rating > 5) {
            setFlash('error', 'Vui lòng chọn số sao từ 1 đến 5.');
            redirect('orders/show', ['id' => $orderId]);
        }

        if (mb_strlen($comment) > 1000) {
            setFlash('error', 'Nội dung đánh giá không được quá 1.000 ký tự.');
            redirect('orders/show', ['id' => $orderId]);
        }

        try {
            $this->reviewModel->createFromCompletedOrderItem(
                $orderItemId,
                (int) currentClient()['user_id'],
                $rating,
                $comment
            );
            setFlash('success', 'Cảm ơn bạn đã đánh giá sản phẩm.');
        } catch (DomainException $exception) {
            setFlash('error', $exception->getMessage());
        } catch (Throwable $exception) {
            setFlash('error', 'Không thể lưu đánh giá. Vui lòng thử lại.');
        }

        redirect('orders/show', ['id' => $orderId]);
    }

    /**
     * Không cho tài khoản bị khóa tiếp tục gửi đánh giá bằng session cũ.
     */
    private function ensureActiveClient(): void
    {
        $user = $this->authModel->find((int) currentClient()['user_id']);

        if (!$user || (int) $user['status'] !== 1) {
            unset($_SESSION['client']);
            setFlash('error', 'Tài khoản không tồn tại hoặc đã bị khóa.');
            redirect('login');
        }
    }
}
