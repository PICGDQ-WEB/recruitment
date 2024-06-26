<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\AdminInfo;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogSeo;
use App\Models\category_blog_seo;
use App\Models\GlobalSeo;
use App\Models\PageSeo;
use App\Models\JobDetail;
use App\Models\Functions;
use App\Models\Industry;
use Intervention\Image\Facades\Image;

class backendController extends Controller
{
    function login(Request $req){
        if(Cookie::has('pswd')){
            return redirect('admin/dashboard');
        }else{
            return view('admin.login');
        }
    }
    function checklogin(Request $req){
        
        $dbuser =  AdminInfo::first();
        $dbusername = $dbuser->username;
        $dbemail = $dbuser->email;
        $dbpswd = $dbuser->password;

        $email = $req->input('email');
        $mdpswd = $req->input('pswd');
        $pswd = md5($mdpswd);

        if(($dbusername == $email || $dbemail == $email) && $dbpswd == $pswd){
             
        $cookie = Cookie::make('name', $dbusername);
        Cookie::queue($cookie);
        $cookieemail = Cookie::make('email', $dbemail);
        Cookie::queue($cookieemail);
        $cookie1 = Cookie::make('pswd', $dbpswd);
        Cookie::queue($cookie1);
        return true;
        }
        else{
         return false;
        }
    }
    function logout(Request $req){
        if(Cookie::has('pswd'))
        {
           $ckname = Cookie::forget('name');
           Cookie::queue($ckname);

           $ckemail = Cookie::forget('email');
           Cookie::queue($ckemail);

           $ckpswd = Cookie::forget('pswd');
           Cookie::queue($ckpswd);

           return redirect('admin');
        }
    }
    function dashboard(Request $req){
          if(Cookie::has('pswd')){
            return view('admin.dashboard');
          }
          else{
            return redirect('admin');
          }
    }

    function allblogs(Request $req){
      $search = $req['search'] ?? "";
      if(Cookie::has('pswd')){
        if($search != ''){
          $dbs = DB::table('blogs as b')
          ->select('b.id', 'b.title', 'bc.bcname', 'b.description', 'b.file', 'b.slug')
          ->join('blogs_categories as bc', 'b.category', '=', 'bc.id')
          ->where('b.title', 'LIKE', '%' . $search . '%')
          ->orWhere('bc.bcname', 'LIKE', '%' . $search . '%')
          ->get();
        }
        else{
        $dbs = DB::table('blogs as b')
        ->select('b.id', 'b.title', 'bc.bcname', 'b.description', 'b.file', 'b.slug')
        ->join('blogs_categories as bc', 'b.category', '=', 'bc.id')
        ->get();
        }
        $members = DB::table('blogs as b')
        ->select('b.id', 'b.title', 'bc.bcname', 'b.description', 'b.file', 'b.slug')
        ->join('blogs_categories as bc', 'b.category', '=', 'bc.id')
        ->get();
        return view('admin.blogs.all-blogs',['blog'=>$dbs,'members'=>$members,'search'=>$search]);
      }else{
        return redirect('admin'); 
      }
    }
    function addblog(){
      if(Cookie::has('pswd')){
        $dbs = BlogCategory::all();
        return view('admin.blogs.add-blog',['members'=>$dbs]);
      }
      else{
        return redirect('admin');
      }
    }

 
    function wpaddblog(Request $req){
      if(Cookie::has('pswd')){
        $dbs = new Blog(); 
        $cname = $req->input('title');
        $origname = '';
        if ($req->hasFile('file')) 
        {
        $image = $req->file('file');
        $name = $image->getClientOriginalName();
        $t=time();
        $d=date("Y-m-d",$t);
        $origname = $d."-".$t."-".$name;
        
        $customFolderPath = public_path('blogs');
        if (!file_exists($customFolderPath)) 
        {
        mkdir($customFolderPath, 0755, true);
        } 
        $image->move($customFolderPath, $origname);

        $imagePath = public_path('blogs/' . $origname);
        $imagess = Image::make($imagePath);
        $thumbnail = $imagess->resize(348, 196);
        $thumbnailPath = public_path('blogs-thumb/' . $origname);
        $thumbnail->save($thumbnailPath);
        
        $imagePath = public_path('blogs/' . $origname);
        $imagess = Image::make($imagePath);
        $thumbnail = $imagess->resize(160, 90);
        $thumbnailPath = public_path('recent-blogs-thumb/' . $origname);
        $thumbnail->save($thumbnailPath);
  
        $name = $req->file('file')->getClientOriginalName();
        $origname = $d."-".$t."-".$name;
        }
         
        if($cname){
          $dbs->title = $cname;
          $dbs->description = $req->input('description');
          $dbs->file = $origname;
          $dbs->category = $req->input('category');
          $slug = $req->input('slug');
          $dbs->slug = $slug;
          $result = $dbs->save();
          if($result){
            $blogseo = BlogSeo::create(['canonical'=>$slug,'file'=>$origname,'blogid'=>$dbs->id]);
            $blogseo->id;
            return True;
          }
          else{
            return False;
          }
        }
        else{
          return False;
         }
        }
        else{
          return redirect('admin');
        }
    }

    function editblog($id){
      if(Cookie::has('pswd')){
      $dbs = Blog::find($id);
      $allcats = BlogCategory::all();
      $cats = BlogCategory::all();
      return view('admin.blogs.edit-blog',['members'=>$dbs, 'allcats'=>$allcats, 'cats'=>$cats]);
    }
    else{
      return redirect('admin');
    }
    }

    function UpdateBlog(Request $req)
    {
      if(Cookie::has('pswd')){
      $origname='';
      $dbs = Blog::find($req->id);
  
      if (!$dbs) {
        // Handle product not found error
        return redirect()->back()->with('error', 'Blog not found.');
      }

      
      $slug = $req->slug;
      $dbs->slug = $slug;
    
        // Check if a new image is uploaded for updating
      if ($req->hasFile('file')) {
        $image = $req->file('file');
        $name = $image->getClientOriginalName();
        $t=time();
        $d=date("Y-m-d",$t);
        $origname = $d."-".$t."-".$name;
        $customFolderPath = public_path('blogs');
        $image->move($customFolderPath, $origname);
        $dbs->file = $origname;
        
        $imagePath = public_path('blogs/' . $origname);
        $imagess = Image::make($imagePath);
        $thumbnail = $imagess->resize(348, 196);
        $thumbnailPath = public_path('blogs-thumb/' . $origname);
        $thumbnail->save($thumbnailPath);
        
        $imagePath = public_path('blogs/' . $origname);
        $imagess = Image::make($imagePath);
        $thumbnail = $imagess->resize(160, 90);
        $thumbnailPath = public_path('recent-blogs-thumb/' . $origname);
        $thumbnail->save($thumbnailPath);

        $blogseo = BlogSeo::find($dbs->id);
        $blogseo->update(['file'=>$origname]);
        $blogseo->id;
      }
      
        
      $blogseo = BlogSeo::find($dbs->id);
      $blogseo->update(['canonical'=>$slug]);
      $blogseo->id;

      $dbs->title = $req->title;
      $dbs->description = $req->description;
      $dbs->category = $req->category;
      $result = $dbs->save();
      if($result){
      return true;
      }
      else{
        return false;
      }
    }
    else{
      return redirect('admin');
    }
    }
  
    function DeleteBlog($id){
      if(Cookie::has('pswd')){
      $dbseo = BlogSeo::find($id);
      if($dbseo){
        $dbseo->delete();
      }

      $dbs = Blog::find($id);
      if($dbs){
      $dbs->delete();
      return true;
      }
    }
    else{
      return redirect('admin');
    }
  }
  

    function allcategories(){
      if(Cookie::has('pswd')){
      $dbs = BlogCategory::all();
      $member = BlogCategory::all();
      return view('admin.blogs-categories.all-categories',['blogcategory'=>$dbs, 'members'=>$member]);
      }
      else{
        return redirect('admin');
      }
    }

    function editblogcategories($id){
      if(Cookie::has('pswd')){
      $dbs = BlogCategory::find($id);
      $cats = BlogCategory::all();
      return view('admin.blogs-categories.edit-blog-categories',['members'=>$dbs,'cats'=>$cats]);
    }
    else{
      return redirect('admin');
    }
    }

    
  
    function addcategory(){
      if(Cookie::has('pswd')){
        $dbs = BlogCategory::all();
        return view('admin.blogs-categories.add-category',['members'=>$dbs]);
      }
        else{
          return redirect('admin');
        }
    }

    function wpaddcategory(Request $req){
      if(Cookie::has('pswd')){
        $dbs = new BlogCategory(); 
        $cname = $req->input('bcname');
        $origname = '';
        if ($req->hasFile('bcfile')) 
        {
        $image = $req->file('bcfile');
        $name = $image->getClientOriginalName();
        $t=time();
        $d=date("Y-m-d",$t);
        $origname = $d."-".$t."-".$name;
        
        $customFolderPath = public_path('blogs');
        if (!file_exists($customFolderPath)) 
        {
        mkdir($customFolderPath, 0755, true);
        } 
        $image->move($customFolderPath, $origname);
  
        $name = $req->file('bcfile')->getClientOriginalName();
        $origname = $d."-".$t."-".$name;
        }
  
        if($cname){
          $dbs->bcname = $cname;
          $dbs->bcdescription = $req->input('bcdescription');
          $dbs->bcfile = $origname;
          $dbs->bccategory = $req->input('bccategory');
          $slug = $req->input('bcslug');
          $dbs->bcslug = $slug;
          $result = $dbs->save();
          if($result){
            $blogseo = category_blog_seo::create(['canonical'=>$slug,'file'=>$origname,'blogid'=>$dbs->id]);
            $blogseo->id;
            return True;
          }
          else{
            return False;
          }
                    
        }
        else{
            return False;
         }
        }
        else{
          return redirect('admin');
        }
    }

  
    function UpdateBlogCategory(Request $req)
  {
    if(Cookie::has('pswd')){
    $origname='';
    $dbs = BlogCategory::find($req->id);

    if (!$dbs) {
      // Handle product not found error
      return redirect()->back()->with('error', 'Blog not found.');
    }

    $slug = $req->bcslug;
    $dbs->bcslug = $slug;
  
      // Check if a new image is uploaded for updating
    if ($req->hasFile('bcfile')) {
      $image = $req->file('bcfile');
      $name = $image->getClientOriginalName();
      $t=time();
      $d=date("Y-m-d",$t);
      $origname = $d."-".$t."-".$name;
      $customFolderPath = public_path('blogs');
      $image->move($customFolderPath, $origname);
      $dbs->bcfile = $origname;
      
      $blogseo = category_blog_seo::find($dbs->id);
      $blogseo->update(['file'=>$origname]);
      $blogseo->id;
    }
       
    $blogseo = category_blog_seo::find($dbs->id);
    $blogseo->update(['canonical'=>$slug]);
    $blogseo->id;

    $dbs->bcname = $req->bcname;
    
    $dbs->bcdescription = $req->bcdescription;
    $dbs->bccategory = $req->bccategory;
    $result = $dbs->save();
    if($result){
    return true;
    }
    else{
      return false;
    }
  }
  else{
    return redirect('admin');
  }
  }

  function DeleteBlogCategory($id){
    if(Cookie::has('pswd')){
    
    $dbseo = category_blog_seo::find($id);
      if($dbseo){
        $dbseo->delete();
     }

    $dbs = BlogCategory::find($id);
    if($dbs){
    $dbs->delete();
    return true;
    }
  }
  else{
    return redirect('admin');
  }
}

  function postseo($id){
    if(Cookie::has('pswd')){
    $dbs = BlogSeo::find($id);
    return view('admin.seo',['blogid'=>$id,'members'=>$dbs]);
  }
  else{
    return redirect('admin');
  }
  }

  function wpaddpostseo(Request $req){
    if(Cookie::has('pswd')){
    $dbs = BlogSeo::find($req->blogid);
    if (!$dbs) {
      // Handle product not found error
      return redirect()->back()->with('error', 'Blog not found.');
    }
    
    $dbs->title = $req->title;
    $dbs->description = $req->description;
    $dbs->keywords = $req->keywords;
    $dbs->author = $req->author;
    $dbs->smarkup = $req->smarkup;
    $result = $dbs->save();
    if($result){
    return true;
    }
    else{
      return false;
    } 
  }
  else{
    return redirect('admin');
  }
  }

  function postcatseo($id){
    if(Cookie::has('pswd')){
    $dbs = category_blog_seo::find($id);
    return view('admin.cat-seo',['blogid'=>$id,'members'=>$dbs]);
  }
  else{
    return redirect('admin');
  }
  }

  function wpaddpostcatseo(Request $req){
    if(Cookie::has('pswd')){
    $dbs = category_blog_seo::find($req->blogid);
    if (!$dbs) {
      // Handle product not found error
      return redirect()->back()->with('error', 'Blog Category not found.');
    }
    
    $dbs->title = $req->title;
    $dbs->description = $req->description;
    $dbs->keywords = $req->keywords;
    $dbs->author = $req->author;
    $dbs->smarkup = $req->smarkup;
    $result = $dbs->save();
    if($result){
    return true;
    }
    else{
      return false;
    } 
  }
  else{
    return redirect('admin');
  }
  }

  function globalseo(){
    if(Cookie::has('pswd')){
        $gseo = GlobalSeo::find(1);
        return view('admin.globalseo',['gseo'=>$gseo]);
    }
    else{
        return redirect('admin');
    }
  }

  function wpglobalseo(Request $req){
    if(Cookie::has('pswd')){
    $gseo = GlobalSeo::find(1);
    $gseo->sitename = $req->sitename;
    $gseo->facebook = $req->facebook;
    $gseo->youtube = $req->youtube;
    $gseo->instagram = $req->instagram;
    $gseo->twitter = $req->twitter;
    $gseo->linkedin = $req->linkedin;
    $gseo->whatsapp = $req->whatsapp;
    $gseo->pinterest = $req->pinterest;
    $gseo->globalheader = $req->globalheader;
    $gseo->gfbs = $req->gfbs;
    $gseo->gfas = $req->gfas;
    $result = $gseo->save();
    return $result;
  }
  else{
    return redirect('admin');
  }
  }

  function admininfo(Request $req){
    if(Cookie::has('pswd')){
    $admin = AdminInfo::first();
    return view('admin.admin-info',['admin'=>$admin]);
  }
  else{
    return redirect('admin');
  }
  }

  function wpadmininfo(Request $req){
    if(Cookie::has('pswd')){
    $admin = AdminInfo::find($req->id);
    $adminseo = GlobalSeo::find($req->id);

    if($adminseo){
      $data = GlobalSeo::find($req->id);
      $data->ownername = $req->username;
      $data->save();

      $admin->username = $req->username;
      $admin->password = md5($req->password);
      $admin->save();
      return true;
    }
    else{
      $data = new GlobalSeo();
      $data->ownername = $req->username;
      $data->save();
      $admin->username = $req->username;
      $admin->password = md5($req->password);
      $admin->save();
      return true;
    }
  }
  else{
    return redirect('admin');
  }
  }

  function pageseo(){
    if(Cookie::has('pswd')){
    $data = PageSeo::all();
    return view('admin.pageseo',['data'=>$data]);
     }
    else{
      return redirect('admin');
    }
  }

  function getpage(){
    if(Cookie::has('pswd')){
    return view('admin.pages.add-page');
  }
  else{
    return redirect('admin');
  }
  }

  function addpage(Request $req){
    if(Cookie::has('pswd')){
    $dbs = new PageSeo();
    $dbs->pagename = $req->pagename;
    $dbs->slug = $req->slug;
    $result = $dbs->save();
    if($result){
      return true;
    }
    else{
      return false;
    }
  }
  else{
    return redirect('admin');
  }

  }

  function geteditpage($id){
    if(Cookie::has('pswd')){
    $data = PageSeo::find($id);
    return view('admin.pages.page-seo-form',['members'=>$data]);
  }
  else{
    return redirect('admin');
  }
  }

  function updatepageseo(Request $req){
    if(Cookie::has('pswd')){
    $origname='';
    $dbs = PageSeo::find($req->id);
    
    if (!$dbs) {
      // Handle product not found error
      return redirect()->back()->with('error', 'Page not found.');
    }
  
      // Check if a new image is uploaded for updating
    if ($req->hasFile('file')) {
      $image = $req->file('file');
      $name = $image->getClientOriginalName();
      $t=time();
      $d=date("Y-m-d",$t);
      $origname = $d."-".$t."-".$name;
      $customFolderPath = public_path('pages');
      $image->move($customFolderPath, $origname);
      $dbs->file = $origname;
    }
       
    
    $dbs->pagename = $req->pagename;
    $dbs->slug = $req->slug;
    $dbs->title = $req->title;
    $dbs->description = $req->description;
    $dbs->keywords = $req->keywords;
    $dbs->author = $req->author;
    $dbs->smarkup = $req->smarkup;
    $result = $dbs->save();
    if($result){
    return true;
    }
    else{
      return false;
    }
  }
  else{
    return redirect('admin');
  }
  }

  function deletepageseo($id){
    if(Cookie::has('pswd')){
    $dbs = PageSeo::find($id);
    if($dbs){
    $dbs->delete();
    return true;
    }
  }
  else{
    return redirect('admin');
  }
  }


  // Job Details

  function alljobs(){
    $search = $req['search'] ?? "";
    if(Cookie::has('pswd')){
      if($search != ''){
        $search = "Data";
      }
      else{
      $dbs = JobDetail::get();
      }
      return view('admin.job_details.all-jobs',['jobs'=>$dbs,'search'=>$search]);
    }else{
      return redirect('admin'); 
    }
  }
  function addjobdetail(){
    if(Cookie::has('pswd')){
      return view('admin.job_details.add-jobdetail');
    }
    else{
      return redirect('admin');
    }
  }


  function wpaddjobdetail(Request $req){
    if(Cookie::has('pswd')){
      $dbs = new Blog(); 
      $cname = $req->input('title');
      $origname = '';
      if ($req->hasFile('file')) 
      {
      $image = $req->file('file');
      $name = $image->getClientOriginalName();
      $t=time();
      $d=date("Y-m-d",$t);
      $origname = $d."-".$t."-".$name;
      
      $customFolderPath = public_path('blogs');
      if (!file_exists($customFolderPath)) 
      {
      mkdir($customFolderPath, 0755, true);
      } 
      $image->move($customFolderPath, $origname);

      $imagePath = public_path('blogs/' . $origname);
      $imagess = Image::make($imagePath);
      $thumbnail = $imagess->resize(348, 196);
      $thumbnailPath = public_path('blogs-thumb/' . $origname);
      $thumbnail->save($thumbnailPath);
      
      $imagePath = public_path('blogs/' . $origname);
      $imagess = Image::make($imagePath);
      $thumbnail = $imagess->resize(160, 90);
      $thumbnailPath = public_path('recent-blogs-thumb/' . $origname);
      $thumbnail->save($thumbnailPath);

      $name = $req->file('file')->getClientOriginalName();
      $origname = $d."-".$t."-".$name;
      }
       
      if($cname){
        $dbs->title = $cname;
        $dbs->description = $req->input('description');
        $dbs->file = $origname;
        $dbs->category = $req->input('category');
        $slug = $req->input('slug');
        $dbs->slug = $slug;
        $result = $dbs->save();
        if($result){
          $blogseo = BlogSeo::create(['canonical'=>$slug,'file'=>$origname,'blogid'=>$dbs->id]);
          $blogseo->id;
          return True;
        }
        else{
          return False;
        }
      }
      else{
        return False;
       }
      }
      else{
        return redirect('admin');
      }
  }

  function editJobdetail($id){
    if(Cookie::has('pswd')){
    $dbs = Blog::find($id);
    $allcats = BlogCategory::all();
    $cats = BlogCategory::all();
    return view('admin.blogs.edit-blog',['members'=>$dbs, 'allcats'=>$allcats, 'cats'=>$cats]);
  }
  else{
    return redirect('admin');
  }
  }

  function UpdateJobdetail(Request $req)
  {
    if(Cookie::has('pswd')){
    $origname='';
    $dbs = Blog::find($req->id);

    if (!$dbs) {
      // Handle product not found error
      return redirect()->back()->with('error', 'Blog not found.');
    }

    
    $slug = $req->slug;
    $dbs->slug = $slug;
  
      // Check if a new image is uploaded for updating
    if ($req->hasFile('file')) {
      $image = $req->file('file');
      $name = $image->getClientOriginalName();
      $t=time();
      $d=date("Y-m-d",$t);
      $origname = $d."-".$t."-".$name;
      $customFolderPath = public_path('blogs');
      $image->move($customFolderPath, $origname);
      $dbs->file = $origname;
      
      $imagePath = public_path('blogs/' . $origname);
      $imagess = Image::make($imagePath);
      $thumbnail = $imagess->resize(348, 196);
      $thumbnailPath = public_path('blogs-thumb/' . $origname);
      $thumbnail->save($thumbnailPath);
      
      $imagePath = public_path('blogs/' . $origname);
      $imagess = Image::make($imagePath);
      $thumbnail = $imagess->resize(160, 90);
      $thumbnailPath = public_path('recent-blogs-thumb/' . $origname);
      $thumbnail->save($thumbnailPath);

      $blogseo = BlogSeo::find($dbs->id);
      $blogseo->update(['file'=>$origname]);
      $blogseo->id;
    }
    
      
    $blogseo = BlogSeo::find($dbs->id);
    $blogseo->update(['canonical'=>$slug]);
    $blogseo->id;

    $dbs->title = $req->title;
    $dbs->description = $req->description;
    $dbs->category = $req->category;
    $result = $dbs->save();
    if($result){
    return true;
    }
    else{
      return false;
    }
  }
  else{
    return redirect('admin');
  }
  }

  function DeleteJobdetail($id){
    if(Cookie::has('pswd')){
    $dbseo = BlogSeo::find($id);
    if($dbseo){
      $dbseo->delete();
    }

    $dbs = Blog::find($id);
    if($dbs){
    $dbs->delete();
    return true;
    }
  }
  else{
    return redirect('admin');
  }
}


// All Functions
  
function allfunctions(){
  $search = $req['search'] ?? "";
  if(Cookie::has('pswd')){
    if($search != ''){
      $search = "Data";
    }
    else{
    $dbs = Functions::get();
    }
    return view('admin.functions.all-functions',['function'=>$dbs,'search'=>$search]);
  }else{
    return redirect('admin'); 
  }
}

function wpaddfunction(Request $req){
  if(Cookie::has('pswd')){
    $dbs = new Functions(); 
    $cname = $req->input('function_name');
    
    if($cname){
      $dbs->function_name = $cname;
      $result = $dbs->save();
      if($result){
        return True;
      }
      else{
        return False;
      }
    }
    else{
      return False;
     }
    }
    else{
      return redirect('admin');
    }
}

function editfunction($id){
  if(Cookie::has('pswd')){
  $dbs = Functions::find($id);
  return view('admin.functions.edit-function',['members'=>$dbs]);
}
else{
  return redirect('admin');
}
}

function Updatefunction(Request $req)
{
  if(Cookie::has('pswd')){
  $origname='';
  $dbs = Functions::find($req->id);

  if (!$dbs) {
    // Handle product not found error
    return redirect()->back()->with('error', 'Blog not found.');
  }

  $dbs->function_name = $req->function_name;
  $result = $dbs->save();
  if($result){
  return true;
  }
  else{
    return false;
  }
}
else{
  return redirect('admin');
}
}

function Deletefunction($id){
  if(Cookie::has('pswd')){
 
  $dbs = Functions::find($id);
  if($dbs){
  $dbs->delete();
  return true;
  }
}
else{
  return redirect('admin');
}
}

// All Industry
 
function allIndustries(){
  $search = $req['search'] ?? "";
  if(Cookie::has('pswd')){
    if($search != ''){
      $search = "Data";
    }
    else{
    $dbs = Industry::get();
    }
    return view('admin.industries.all-industries',['industry'=>$dbs,'search'=>$search]);
  }else{
    return redirect('admin'); 
  }
}

function wpaddIndustry(Request $req){
  if(Cookie::has('pswd')){
    $dbs = new Industry(); 
    $cname = $req->input('industry_name');
    
    if($cname){
      $dbs->industry_name = $cname;
      $result = $dbs->save();
      if($result){
        return True;
      }
      else{
        return False;
      }
    }
    else{
      return False;
     }
    }
    else{
      return redirect('admin');
    }
}

function editIndustry($id){
  if(Cookie::has('pswd')){
  $dbs = Industry::find($id);
  return view('admin.industries.edit-industry',['members'=>$dbs]);
}
else{
  return redirect('admin');
}
}

function UpdateIndustry(Request $req)
{
  if(Cookie::has('pswd')){
  $origname='';
  $dbs = Industry::find($req->id);

  if (!$dbs) {
    // Handle product not found error
    return redirect()->back()->with('error', 'Industry not found.');
  }

  $dbs->industry_name = $req->industry_name;
  $result = $dbs->save();
  if($result){
  return true;
  }
  else{
    return false;
  }
}
else{
  return redirect('admin');
}
}

function DeleteIndustry($id){
  if(Cookie::has('pswd')){
 
  $dbs = Industry::find($id);
  if($dbs){
  $dbs->delete();
  return true;
  }
}
else{
  return redirect('admin');
}
}



}