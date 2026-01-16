<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Đăng nhập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="icon-close-circle"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="d-flex align-items-center justify-content-center g_id_signin" style="min-height: 200px">
                    <a href="" class="google btn" id="google-authen-btn">
                        <ion-icon name="logo-google"></ion-icon> Login with Google
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#google-authen-btn').on('click', (e) => {
        e.preventDefault();
        // Lưu URL hiện tại để redirect về sau khi đăng nhập
        const currentUrl = window.location.href;
        window.location.href = '{{ route("auth.google") }}?state=' + encodeURIComponent(currentUrl);
    });
</script>
