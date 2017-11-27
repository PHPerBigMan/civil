<?php
// +----------------------------------------------------------------------
//            -------------------------
//           /   / ----------------\  \
//          /   /             \  \
//         /   /              /  /
//        /   /    /-------------- /  /
//       /   /    /-------------------\  \
//      /   /                   \  \
//     /   /                     \  \
//    /   /                      /  /
//   /   /      /----------------------- /  /
//  /-----/      /---------------------------/
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://baimifan.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanglidong
// | Date: 2017/2/7
// | Time: 10:41
// +----------------------------------------------------------------------


namespace app\index\controller;



use EasyWeChat\Foundation\Application;
use think\Controller;

class TinyImg
{
    public function compressImgsFolder($key,$inputFolder,$outputFolder){
        $images = $this->getFiles($inputFolder);
        if(empty($images)){
          return false;
        }
        foreach($images as $image){
                         $input = $inputFolder."\\".$image;
             $output = $outputFolder."\\".$image;
             print($input."<br>");
             print($output."<br>");
             $this->compressImg($key,$input,$output);
        }
        return true;
    }

    public function compressImg($key, $input, $output){
        $url = "https://api.tinify.com/shrink";
                 $options = array(
                     "http" => array(
                     "method" => "POST",
                     "header" => array(
                     "Content-type: image/png",
                     "Authorization: Basic " . base64_encode("api:$key")                 ),
                     "content" => file_get_contents($input)
             ),
             "ssl" => array(
                 "cafile" => __DIR__ . "/cacert.pem",
                 "verify_peer" => true
             )
         );
        $result = fopen($url, "r", false, stream_context_create($options));
                 if ($result) {
             foreach ($http_response_header as $header) {
                    if (strtolower(substr($header, 0, 10)) === "location: ") {
                    file_put_contents($output, fopen(substr($header, 10), "rb", false));
                 }
             }
         } else {
             print("Compression failed<br>");
         }
    }


    public function getFiles($filedir){
         $files = [];
         $dir = @dir($filedir);
         while(($file = $dir->read())!= false){
             if($file != "." and $file != ".."){
                  $files[] = $file;
             }
         }
         $dir->close();
         return $files;
     }
}
