<?php

namespace App\Models;



class post 
{
    private static $blog_posts = [
        [
            "thumbnail" => "not-found-blog.png",
            "title" => "Blog Not Found 1",
            "date" => "Thu, 17 Aug 2023",
            "slug" => "blog-not-found-1",
            "content" => "Lorem ipsum, dolor sit amet consectetur adipisicing elit. Distinctio, repellat voluptate fugiat reprehenderit quod quasi deleniti autem ratione voluptates ab nostrum corporis, modi dicta recusandae, ipsa veritatis aut tempora. Ut totam inventore facilis ullam amet voluptas voluptatibus quae rem pariatur repellat minus hic, veniam tenetur debitis modi minima illo necessitatibus sit quidem nemo placeat aut, voluptatem velit corrupti. Non, blanditiis nihil! Magni, error? Nam, animi esse! Consequuntur, vitae. A, quo quisquam. Natus sequi est iste perspiciatis culpa pariatur odio tenetur."
        ],
        [
            "thumbnail" => "not-found-blog.png",
            "title" => "Blog Not Found 2",
            "date" => "Thu, 17 Aug 2023",
            "slug" => "blog-not-found-2",
            "content" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita neque fugiat reiciendis voluptatem itaque assumenda dolorem asperiores nesciunt placeat voluptatibus corrupti dolore obcaecati similique voluptatum doloribus sapiente modi, id aliquam iste, possimus dignissimos fugit quo accusantium nihil? Ipsam, cum exercitationem impedit qui, vitae hic sunt delectus quos repellat voluptates, voluptate illum natus perferendis aut minima inventore nam! Ea maxime repudiandae obcaecati quisquam ullam incidunt explicabo magni quas, in temporibus dicta corporis atque voluptas quidem? Hic ea repudiandae, eum dolores tenetur natus quam dolore illo iusto alias at eveniet nihil minus dolorum nostrum, officiis, eius nisi recusandae veniam perspiciatis sint perferendis."
        ],
        [
            "thumbnail" => "not-found-blog.png",
            "title" => "Blog Not Found 3",
            "date" => "Thu, 17 Aug 2023",
            "slug" => "blog-not-found-3",
            "content" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita neque fugiat reiciendis voluptatem itaque assumenda dolorem asperiores nesciunt placeat voluptatibus corrupti dolore obcaecati similique voluptatum doloribus sapiente modi, id aliquam iste, possimus dignissimos fugit quo accusantium nihil? Ipsam, cum exercitationem impedit qui, vitae hic sunt delectus quos repellat voluptates, voluptate illum natus perferendis aut minima inventore nam! Ea maxime repudiandae obcaecati quisquam ullam incidunt explicabo magni quas, in temporibus dicta corporis atque voluptas quidem? Hic ea repudiandae, eum dolores tenetur natus quam dolore illo iusto alias at eveniet nihil minus dolorum nostrum, officiis, eius nisi recusandae veniam perspiciatis sint perferendis."
        ],
        [
            "thumbnail" => "not-found-blog.png",
            "title" => "Blog Not Found 4",
            "date" => "Thu, 17 Aug 2023",
            "slug" => "blog-not-found-4",
            "content" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita neque fugiat reiciendis voluptatem itaque assumenda dolorem asperiores nesciunt placeat voluptatibus corrupti dolore obcaecati similique voluptatum doloribus sapiente modi, id aliquam iste, possimus dignissimos fugit quo accusantium nihil? Ipsam, cum exercitationem impedit qui, vitae hic sunt delectus quos repellat voluptates, voluptate illum natus perferendis aut minima inventore nam! Ea maxime repudiandae obcaecati quisquam ullam incidunt explicabo magni quas, in temporibus dicta corporis atque voluptas quidem? Hic ea repudiandae, eum dolores tenetur natus quam dolore illo iusto alias at eveniet nihil minus dolorum nostrum, officiis, eius nisi recusandae veniam perspiciatis sint perferendis."
        ],
        [
            "thumbnail" => "not-found-blog.png",
            "title" => "Blog Not Found 5",
            "date" => "Thu, 17 Aug 2023",
            "slug" => "blog-not-found-5",
            "content" => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita neque fugiat reiciendis voluptatem itaque assumenda dolorem asperiores nesciunt placeat voluptatibus corrupti dolore obcaecati similique voluptatum doloribus sapiente modi, id aliquam iste, possimus dignissimos fugit quo accusantium nihil? Ipsam, cum exercitationem impedit qui, vitae hic sunt delectus quos repellat voluptates, voluptate illum natus perferendis aut minima inventore nam! Ea maxime repudiandae obcaecati quisquam ullam incidunt explicabo magni quas, in temporibus dicta corporis atque voluptas quidem? Hic ea repudiandae, eum dolores tenetur natus quam dolore illo iusto alias at eveniet nihil minus dolorum nostrum, officiis, eius nisi recusandae veniam perspiciatis sint perferendis."
        ],
    ];

    public static function all()
    {
        return collect(self::$blog_posts);
    }

    public static function rev()
    {
        $blogs = static::all();

        return $blogs->reverse();
    }

    public static function lastPost()
    {
        $blogs = static::all();

        $lastPost = $blogs->reverse()->take(3);

        return $lastPost;
    }

    public static function find($slug)
    {
        $blogs = static::all();

        // $blog = [];
        // foreach($blogs as $post) {
        //     if($post["slug"] === $slug) {
        //         $blog = $post;
        //     }
        // }

        return $blogs->firstWhere('slug', $slug);
    }

}
