<!-- Full Description Modal -->
<div class="modal fade" id="fullDescriptionModal" tabindex="-1" aria-labelledby="fullDescriptionModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-description modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullDescriptionModalLabel">Mô tả:</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="icon-close-circle"></i>
                </button>
            </div>
            <div class="modal-body">
                <p><span style="font-size: 16px">{{ $fullDescription ?? 'Bộ phim kể về Phó Hiệu trưởng Hiroshi Uchiyamada, người vô tình lạc vào một cơn ác mộng xuyên không gian sau khi đến Kabukicho để tìm kiếm nữ sinh mất tích Nanami.' }}</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="ReportModal" tabindex="-1" aria-labelledby="ReportModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ReportModalLabel">Báo cáo lỗi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="icon-close-circle"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="report-form">
                    <textarea class="form-control form-control-textarea" name="content" maxlength="3000" placeholder="Gặp vấn đề khác xin điền ở đây..."></textarea>
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#otp-modal">Báo cáo</button>
                </form>
            </div>
        </div>
    </div>
</div>
