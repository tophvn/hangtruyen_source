@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Chính sách bảo mật</span>
    </div>

    <div class="page-content">
        <h2>1. Giới Thiệu</h2>
        <p>Chào mừng bạn đến với <strong>{{ request()->getHost() }}</strong>. Chúng tôi cam kết bảo vệ quyền riêng tư và thông tin cá nhân của bạn khi truy cập và sử dụng dịch vụ trên trang web này. Chính sách bảo mật này giải thích cách chúng tôi thu thập, sử dụng và bảo vệ thông tin của bạn.</p>
        
        <h2>2. Thông Tin Chúng Tôi Thu Thập</h2>
        <p>Khi bạn sử dụng {{ request()->getHost() }}, chúng tôi có thể thu thập các loại thông tin sau:</p>
        <ul>
            <li><strong>Thông tin cá nhân</strong>: Tên, email, số điện thoại (nếu có cung cấp khi đăng ký hoặc liên hệ với chúng tôi).</li>
            <li><strong>Thông tin thiết bị</strong>: Địa chỉ IP, loại trình duyệt, hệ điều hành, thời gian truy cập.</li>
            <li><strong>Cookies và công nghệ theo dõi</strong>: Dữ liệu cookie để cải thiện trải nghiệm người dùng và tối ưu hóa nội dung hiển thị.</li>
        </ul>
        
        <h2>3. Cách Chúng Tôi Sử Dụng Thông Tin</h2>
        <p>Chúng tôi sử dụng thông tin thu thập được để:</p>
        <ul>
            <li>Cung cấp, duy trì và cải thiện dịch vụ.</li>
            <li>Đáp ứng yêu cầu hỗ trợ khách hàng.</li>
            <li>Phân tích, thống kê để cải thiện chất lượng nội dung.</li>
            <li>Ngăn chặn các hoạt động gian lận hoặc vi phạm chính sách sử dụng.</li>
        </ul>
        
        <h2>4. Chia Sẻ Thông Tin Với Bên Thứ Ba</h2>
        <p>Chúng tôi <strong>không</strong> bán, trao đổi hoặc chia sẻ thông tin cá nhân của bạn với bên thứ ba trừ các trường hợp:</p>
        <ul>
            <li>Khi có sự đồng ý của bạn.</li>
            <li>Theo yêu cầu của cơ quan chức năng theo quy định pháp luật.</li>
            <li>Để bảo vệ quyền lợi, tài sản hoặc an toàn của {{ request()->getHost() }} và người dùng.</li>
        </ul>
        
        <h2>5. Bảo Mật Thông Tin</h2>
        <p>Chúng tôi áp dụng các biện pháp bảo mật hợp lý để bảo vệ thông tin cá nhân của bạn, bao gồm mã hóa dữ liệu và giới hạn quyền truy cập. Tuy nhiên, không có hệ thống nào an toàn tuyệt đối, vì vậy bạn cần cẩn trọng khi chia sẻ thông tin cá nhân trên môi trường trực tuyến.</p>
        
        <h2>6. Quyền Lợi Của Người Dùng</h2>
        <p>Bạn có quyền:</p>
        <ul>
            <li>Yêu cầu truy cập, chỉnh sửa hoặc xóa thông tin cá nhân của mình.</li>
            <li>Từ chối nhận các thông báo quảng cáo từ chúng tôi.</li>
            <li>Hạn chế hoặc phản đối việc xử lý thông tin cá nhân trong một số trường hợp nhất định.</li>
        </ul>
        
        <h2>7. Cookies và Công Nghệ Theo Dõi</h2>
        <p>Trang web có thể sử dụng <strong>cookies</strong> để cải thiện trải nghiệm người dùng. Bạn có thể quản lý hoặc từ chối cookie thông qua cài đặt trình duyệt của mình.</p>
        
        <h2>8. Thay Đổi Chính Sách Bảo Mật</h2>
        <p>Chúng tôi có thể cập nhật chính sách này theo thời gian. Khi có thay đổi, chúng tôi sẽ thông báo trên trang web. Việc tiếp tục sử dụng dịch vụ sau khi chính sách được cập nhật đồng nghĩa với việc bạn đồng ý với các thay đổi đó.</p>
        
        <h2>9. Liên Hệ</h2>
        @php
            $gmail = \App\Models\Setting::get('gmail_url', '');
        @endphp
        <p>Nếu bạn có bất kỳ câu hỏi nào về Chính Sách Bảo Mật, vui lòng liên hệ với chúng tôi qua email: <strong>{{ $gmail ?: 'contact.hangtruyen@gmail.com' }}</strong>.</p>
        <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của <strong>{{ request()->getHost() }}</strong>!</p>
    </div>
</div>
@endsection
