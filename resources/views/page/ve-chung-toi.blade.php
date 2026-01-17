@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Về chúng tôi</span>
    </div>

    <div class="page-content">
        <h2>1. Chúng Tôi Là Ai?</h2>
        <p>Chào mừng bạn đến với <strong>{{ request()->getHost() }}</strong> – nền tảng đọc <a href="{{ url('/') }}" rel="noopener noreferrer" target="_blank">truyện tranh</a> trực tuyến dành cho tất cả những ai yêu thích thế giới truyện tranh phong phú. Chúng tôi không chỉ là một website đọc truyện mà còn là một cộng đồng dành cho những người đam mê manga, manhua, manhwa và truyện tranh Việt Nam.</p>
        <p><br></p>
        
        <h2>2. Sứ Mệnh Của Chúng Tôi</h2>
        <p>Tại <strong>{{ request()->getHost() }}</strong>, chúng tôi cam kết mang đến cho độc giả:</p>
        <ul>
            <li><strong>Kho truyện đa dạng</strong>: Cập nhật liên tục các thể loại truyện từ hành động, phiêu lưu, lãng mạn, hài hước cho đến kinh dị, trinh thám.</li>
            <li><strong>Trải nghiệm đọc truyện mượt mà</strong>: Giao diện thân thiện, dễ sử dụng, giúp người đọc tận hưởng trọn vẹn nội dung truyện yêu thích.</li>
            <li><strong>Cộng đồng kết nối</strong>: Tạo không gian giao lưu, chia sẻ giữa những người có chung niềm đam mê truyện tranh.</li>
        </ul>
        <p><br></p>
        
        <h2>3. Giá Trị Cốt Lõi</h2>
        <ul>
            <li><strong>Miễn phí &amp; dễ dàng truy cập</strong>: Người dùng có thể đọc truyện thoải mái mà không cần đăng ký tài khoản.</li>
            <li><strong>Cập nhật nhanh chóng</strong>: Chúng tôi luôn cố gắng cập nhật những chương truyện mới nhất để phục vụ độc giả.</li>
            <li><strong>Tôn trọng bản quyền</strong>: Chúng tôi luôn lắng nghe phản hồi từ các tác giả và nhà xuất bản để đảm bảo quyền lợi của họ.</li>
        </ul>
        <p><br></p>
        
        <h2>4. Liên Hệ Với Chúng Tôi</h2>
        <p>Chúng tôi luôn mong muốn lắng nghe ý kiến đóng góp từ cộng đồng để ngày càng hoàn thiện và phát triển hơn. Nếu bạn có bất kỳ câu hỏi hoặc đề xuất nào, vui lòng liên hệ qua:</p>
        <ul>
            @php
                $gmail = \App\Models\Setting::get('gmail_url', '');
                $facebookUrl = \App\Models\Setting::get('facebook_url', '');
            @endphp
            @if($gmail)
                <li><strong>Email</strong>: <a href="mailto:{{ $gmail }}">{{ $gmail }}</a></li>
            @else
                <li><strong>Email</strong>: </li>
            @endif
            @if($facebookUrl)
                <li><strong>Fanpage</strong>: <a href="{{ $facebookUrl }}" target="_blank" rel="nofollow">{{ $facebookUrl }}</a></li>
            @else
                <li><strong>Fanpage</strong>: </li>
            @endif
        </ul>
        <p>Cảm ơn bạn đã đồng hành cùng <strong>{{ request()->getHost() }}</strong>! Hãy cùng chúng tôi khám phá thế giới truyện tranh tuyệt vời ngay hôm nay!</p>
    </div>
</div>
@endsection
