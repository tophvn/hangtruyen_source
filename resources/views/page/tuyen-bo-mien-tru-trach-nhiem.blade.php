@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Tuyên bố miễn trừ trách nhiệm</span>
    </div>

    <div class="page-content">
        <h2>1. Giới Thiệu</h2>
        <p>Chào mừng bạn đến với <strong>{{ request()->getHost() }}</strong>! Bằng việc truy cập và sử dụng trang web này, bạn đồng ý với nội dung trong <strong>Tuyên Bố Miễn Trừ Trách Nhiệm</strong> này. Nếu bạn không đồng ý với bất kỳ phần nào của tuyên bố, vui lòng ngừng sử dụng dịch vụ của chúng tôi.</p>
        
        <h2>2. Nội Dung Trên Website</h2>
        <ul>
            <li><strong>{{ request()->getHost() }}</strong> là một nền tảng đọc truyện tranh trực tuyến được tổng hợp từ nhiều nguồn khác nhau.</li>
            <li>Chúng tôi <strong>không sở hữu bản quyền</strong> của bất kỳ truyện nào, trừ khi có quy định cụ thể. Mọi quyền sở hữu trí tuệ thuộc về tác giả và đơn vị xuất bản.</li>
            @php
                $gmail = \App\Models\Setting::get('gmail_url', '');
            @endphp
            <li>Nếu bạn là chủ sở hữu bản quyền và cho rằng nội dung trên website vi phạm quyền lợi của bạn, vui lòng liên hệ với chúng tôi qua email: <strong>{{ $gmail ?: 'contact.hangtruyen@gmail.com' }}</strong> để yêu cầu gỡ bỏ nội dung.</li>
        </ul>
        
        <h2>3. Tính Chính Xác Của Nội Dung</h2>
        <ul>
            <li>Chúng tôi không đảm bảo rằng tất cả nội dung trên website là <strong>chính xác, đầy đủ hoặc cập nhật</strong>. Nội dung có thể thay đổi, chỉnh sửa hoặc bị xóa bất cứ lúc nào mà không cần thông báo trước.</li>
            <li>Mọi thông tin, hình ảnh, hoặc nội dung đăng tải trên <strong>{{ request()->getHost() }}</strong> chỉ mang tính chất tham khảo và giải trí.</li>
        </ul>
        
        <h2>4. Miễn Trừ Trách Nhiệm Pháp Lý</h2>
        <ul>
            <li>Chúng tôi không chịu trách nhiệm đối với bất kỳ <strong>thiệt hại trực tiếp, gián tiếp, ngẫu nhiên hoặc hậu quả nào</strong> do việc sử dụng hoặc không thể sử dụng dịch vụ của <strong>{{ request()->getHost() }}</strong>.</li>
            <li>Người dùng tự chịu trách nhiệm khi truy cập và sử dụng nội dung trên website.</li>
            <li>Chúng tôi không đảm bảo rằng website sẽ hoạt động <strong>liên tục, không bị gián đoạn hoặc không có lỗi</strong>.</li>
        </ul>
        
        <h2>5. Liên Kết Đến Bên Thứ Ba</h2>
        <ul>
            <li><strong>{{ request()->getHost() }}</strong> có thể chứa các liên kết đến website của bên thứ ba. Chúng tôi <strong>không kiểm soát và không chịu trách nhiệm</strong> về nội dung, chính sách bảo mật hoặc hoạt động của các trang web này.</li>
            <li>Việc sử dụng các trang web bên thứ ba hoàn toàn do bạn tự chịu rủi ro.</li>
        </ul>
        
        <h2>6. Thay Đổi Tuyên Bố Miễn Trừ Trách Nhiệm</h2>
        <ul>
            <li>Chúng tôi có quyền sửa đổi hoặc cập nhật nội dung của tuyên bố này bất cứ lúc nào mà không cần thông báo trước.</li>
            <li>Việc tiếp tục sử dụng <strong>{{ request()->getHost() }}</strong> sau khi có thay đổi đồng nghĩa với việc bạn chấp nhận các điều khoản mới.</li>
        </ul>
        
        <h2>7. Liên Hệ</h2>
        <p>Nếu bạn có bất kỳ câu hỏi hoặc khiếu nại nào liên quan đến <strong>Tuyên Bố Miễn Trừ Trách Nhiệm</strong>, vui lòng liên hệ với chúng tôi qua email: <strong>{{ $gmail ?: 'contact.hangtruyen@gmail.com' }}</strong>.</p>
        <p>Cảm ơn bạn đã sử dụng <strong>{{ request()->getHost() }}</strong>!</p>
    </div>
</div>
@endsection
