<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function index()
    {
        // home page
        return view('home', [
            "title" => "Home",
            "name" => "SATRIA RANGGA JATI",
            "biography" => "Satria Rangga Jati is a Front End Web Developer who started his career in the field in mid-2022. He is currently 21 years old. He graduated from Vocational High School majoring in Computer and Networking Engineering from SMK Negeri 4 Kota Bekasi in 2021. Since then, he has gained the basics of web design and development, allowing him to delve into advanced levels of various programming languages used in front-end development.
            As a developer, Satria is always excited to keep learning and keeping up with the latest trends in web technology. He constantly looks for opportunities to improve his skills in web development and contribute to creating exceptional online experiences for users.
            Satria focuses on user experience (UX) and ensures that every element on the web page leaves a positive impression and is user-friendly. Additionally, he ensures that websites are responsive, making them accessible smoothly on various devices such as desktop computers, tablets, and smartphones.
            As a talented young developer, Satria has successfully built a diverse and captivating project portfolio. He enjoys trying out new technologies and using them to create innovative and creative solutions.
            Each web project is a challenge for Satria to create something unique and appealing. He is committed to delivering the best results for clients and end-users, while continuously developing his skills in web development.
            With passion and dedication as a Front End Web Developer, Satria looks forward to exploring his potential and creativity further in developing captivating and inspiring web solutions. He hopes to continue contributing to creating impressive web experiences that add value for users. If you need assistance with web development projects, Satria is more than happy to help!",
            "passionTitle" => "FRONT END WEB DEVELOPER",
            "descPassion" => "As a Front End Web Developer, Satria's task is to design and implement the front-end appearance of a website. He utilizes programming languages such as HTML, CSS, and JavaScript to create layouts, designs, and functionalities that can be enjoyed by users. Satria focuses on the user experience (UX) aspect and ensures that every element on the web page leaves a positive impression and is user-friendly. Additionally, he ensures that the website is responsive, allowing smooth access on various devices, such as desktop computers, tablets, and smartphones. Satria is always eager to continue learning and stay up-to-date with the latest developments in the web development industry. He enjoys experimenting with new technologies and leveraging them to create innovative and creative solutions. Each web project is a challenge for him to create something unique and captivating. Satria is committed to delivering the best outcomes for clients and end-users, as well as continuously developing his skills in web development. He aspires to continue contributing to the creation of impressive web experiences that add value to users. If you need assistance with your web development project, he is more than happy to help!",
            "posts" => Post::lastPost(),
        ]);
        // home page
    }

    public function bio()
    {
        // about page
        return view('about', [
            "title" => "Bio",
            "image" => "IMG_1666.jpg",
            "biography" => "Satria Rangga Jati is a Front End Web Developer who started his career in the field in mid-2022. He is currently 21 years old. He graduated from Vocational High School majoring in Computer and Networking Engineering from SMK Negeri 4 Kota Bekasi in 2021. Since then, he has gained the basics of web design and development, allowing him to delve into advanced levels of various programming languages used in front-end development.
            <br><br>
            As a developer, Satria is always excited to keep learning and keeping up with the latest trends in web technology. He constantly looks for opportunities to improve his skills in web development and contribute to creating exceptional online experiences for users.
            <br><br>
            Satria focuses on user experience (UX) and ensures that every element on the web page leaves a positive impression and is user-friendly. Additionally, he ensures that websites are responsive, making them accessible smoothly on various devices such as desktop computers, tablets, and smartphones.
            <br><br>
            As a talented young developer, Satria has successfully built a diverse and captivating project portfolio. He enjoys trying out new technologies and using them to create innovative and creative solutions.
            <br><br>
            Each web project is a challenge for Satria to create something unique and appealing. He is committed to delivering the best results for clients and end-users, while continuously developing his skills in web development.
            <br><br>
            With passion and dedication as a Front End Web Developer, Satria looks forward to exploring his potential and creativity further in developing captivating and inspiring web solutions. He hopes to continue contributing to creating impressive web experiences that add value for users. If you need assistance with web development projects, Satria is more than happy to help!"
        ]);
        // about page
    }

    public function projects()
    {
        return view('projects',[
            "title" => "Projects",
        ]);
    }

    public function blog()
    {
        // blog page
        return view('posts', [
            "title" => "Blog",
            "posts" => Post::rev(),
        ]);
        // blog page
    }

    public function certificates()
    {
        return view('certificates',[
            "title" => "Certificates",
        ]);
    }

    public function resume()
    {
        return view('resume');
    }

    public function contact()
    {
        return view('contact',[
            "title" => "Contact"
        ]);
    }
    public function blogPost(Post $post)
    {
        return view('post',[
            "title" => "Blog",
            "post" => $post,
        ]);
    }
}
