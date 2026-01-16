@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Điều khoản dịch vụ</span>
    </div>

    <div class="page-content">
        <h2>1. Giới Thiệu</h2>
        <p>Chào mừng bạn đến với <strong>HangTruyen.net</strong>! Bằng cách truy cập và sử dụng trang web này, bạn đồng ý tuân thủ các điều khoản và điều kiện sử dụng sau đây. Nếu bạn không đồng ý với bất kỳ điều khoản nào, vui lòng không tiếp tục sử dụng dịch vụ của chúng tôi.</p>
        
        <h2>2. Quyền Và Trách Nhiệm Của Người Dùng</h2>
        <ul>
            <li>Bạn phải từ <strong>13 tuổi trở lên</strong> để sử dụng trang web.</li>
            <li>Bạn cam kết không sử dụng <strong>HangTruyen.net</strong> vào mục đích vi phạm pháp luật hoặc gây hại cho cá nhân, tổ chức khác.</li>
            <li>Không đăng tải, chia sẻ nội dung vi phạm bản quyền, nội dung phản cảm hoặc trái với thuần phong mỹ tục.</li>
            <li>Bạn có trách nhiệm bảo vệ thông tin tài khoản của mình và chịu trách nhiệm với mọi hoạt động diễn ra trên tài khoản đó.</li>
        </ul>
        
        <h2>3. Nội Dung Và Bản Quyền</h2>
        <ul>
            <li><strong>HangTruyen.net</strong> là nền tảng cung cấp truyện tranh trực tuyến. Chúng tôi <strong>không sở hữu bản quyền</strong> của các truyện đăng tải trên trang web trừ khi có quy định cụ thể.</li>
            <li>Nếu bạn là chủ sở hữu bản quyền và muốn yêu cầu gỡ bỏ nội dung, vui lòng liên hệ với chúng tôi qua email: <strong>contact.hangtruyen@gmail.com</strong>.</li>
        </ul>
        
        <h2>4. Trách Nhiệm Pháp Lý</h2>
        <ul>
            <li>Chúng tôi không chịu trách nhiệm với bất kỳ thiệt hại nào phát sinh do việc sử dụng hoặc không thể sử dụng dịch vụ của <strong>HangTruyen.net</strong>.</li>
            <li>Chúng tôi có quyền <strong>thay đổi, tạm ngừng hoặc ngừng cung cấp dịch vụ</strong> mà không cần thông báo trước.</li>
        </ul>
        
        <h2>5. Chính Sách Quảng Cáo Và Liên Kết</h2>
        <ul>
            <li><strong>HangTruyen.net</strong> có thể chứa quảng cáo hoặc liên kết đến bên thứ ba. Chúng tôi <strong>không chịu trách nhiệm</strong> với nội dung, sản phẩm hoặc dịch vụ của các bên thứ ba này.</li>
            <li>Việc sử dụng các dịch vụ hoặc sản phẩm của bên thứ ba phải tuân theo chính sách của bên đó.</li>
        </ul>
        
        <h2>6. Tài Khoản Thành Viên</h2>
        <ul>
            <li>Khi đăng ký tài khoản trên <strong>HangTruyen.net</strong>, bạn phải cung cấp thông tin chính xác và đầy đủ.</li>
            <li>Chúng tôi có quyền tạm khóa hoặc xóa tài khoản của bạn nếu phát hiện vi phạm điều khoản dịch vụ.</li>
        </ul>
        
        <h2>7. Sửa Đổi Điều Khoản</h2>
        <ul>
            <li>Chúng tôi có thể thay đổi hoặc cập nhật điều khoản dịch vụ bất cứ lúc nào mà không cần thông báo trước.</li>
            <li>Người dùng có trách nhiệm kiểm tra điều khoản thường xuyên để cập nhật các thay đổi.</li>
        </ul>
        
        <h2>8. Liên Hệ</h2>
        <p>Nếu bạn có bất kỳ câu hỏi hoặc khiếu nại nào liên quan đến điều khoản dịch vụ, vui lòng liên hệ với chúng tôi qua email: <strong>contact.hangtruyen@gmail.com</strong>.</p>
        <p>Cảm ơn bạn đã sử dụng <strong>HangTruyen.net</strong>!</p>
    </div>
</div>
@endsection
