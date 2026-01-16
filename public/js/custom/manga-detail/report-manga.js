const reportModalElem = $('#ReportModal');
const formReasonsReportElem = reportModalElem.find('.report-form');
const submitButtonElem = reportModalElem.find('.report-form button');
const otherReasonElem = reportModalElem.find('textarea');
const reportReasons = [
    {
        id: 'report-reason_1',
        label: 'Chap bị lặp lại',
    },
    {
        id: 'report-reason_2',
        label: 'Số vol/chap bị sai số/thiếu',
    },
    {
        id: 'report-reason_3',
        label: 'Sai mô tả/thông tin truyện',
    },
    {
        id: 'report-reason_4',
        label: 'Thiếu ảnh bìa truyện/ảnh bìa chap nếu có',
    },
    {
        id: 'report-reason_5',
        label: 'Có yếu tố phá hoại (đăng chap troll, dịch bậy, v.v.)',
    },
];

$('a.report').on('click', function () {
    formReasonsReportElem.find('input[type="checkbox"]').prop('checked', false);
    formReasonsReportElem.find('textarea').val('');
});

reportReasons.forEach((reason) => {
    formReasonsReportElem.prepend(`
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="${reason.id}" data-reason="${reason.label}">
            <label class="form-check-label" for="${reason.id}">${reason.label}</label>`);
});

submitButtonElem.on('click', async function (e) {
    e.preventDefault();
    const checkedReasons = formReasonsReportElem.find('input:checked');
    const reasons = checkedReasons
        .map((index, elem) => $(elem).data('reason'))
        .get();

    if (!!otherReasonElem.val()) {
        reasons.push(otherReasonElem.val());
    }

    if (!otherReasonElem.val() && !reasons.length) {
        alert('Vui lòng chọn ít nhất 1 lý do báo cáo');
        return;
    }

    const currentMangaDetail = window.mangaDetail || (typeof mangaDetail !== 'undefined' ? mangaDetail : null);
    if (!currentMangaDetail || !currentMangaDetail.id) {
        alert('Không thể xác định truyện để báo cáo');
        return;
    }

    const response = await postReport(currentMangaDetail.id, reasons);

    if (response && response.status === 'success') {
        const message = response.message || 'Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét và xử lý sớm nhất có thể.';
        
        if (typeof alertNoti === 'function') {
            alertNoti(message);
        } else {
            alert(message);
        }
        
        reportModalElem.modal('hide');
        formReasonsReportElem.find('input[type="checkbox"]').prop('checked', false);
        formReasonsReportElem.find('textarea').val('');
    }
});
