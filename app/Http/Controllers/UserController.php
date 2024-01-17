<?php

namespace App\Http\Controllers;

use App\Exports\MediaExport;
use App\Exports\UserExport;
use App\Models\Repeat;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'user_name' => ['required', 'string', 'max:128'],
            'email' => ['required', 'string', 'email', 'max:128', 'unique:users'],
            'sector_id' => 'required',
            'role_id' => 'required',
            'password' => 'required|min:6|max:15'
        ]);

        User::create([
            'name' => $request->user_name,
            'email' => $request->email,
            'sector_id' => $request->sector_id,
            'role_id' => $request->role_id,
            'birth_date' => $request->birth_date,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return back();
    }

    public function updatePassword(Request $request){
        $request->validate([
            'old_password' => 'required|min:6|max:20',
            'new_password' => 'required|min:6|max:20',
            'confirm_password' => 'required|same:new_password'
        ]);

        $user = Auth::user();
        if(Hash::check($request->old_password, $user->password)){
            $user->update([
                'password' => bcrypt($request->new_password)
            ]);
            return back()->withMessage('Пароль успешно изменен');
        }else{
            return back()->withError("Неправильный пароль");
        }
    }

    public function changeProfilePicture(Request $request){
        $file = $request->file('avatar_img');

        $filename = 'UIMG'.date('Ymd').uniqid().'.jpg';
        $upload = $file->move(public_path("/user_image"), $filename);

        if(!$upload ){
            return response()->json(['status'=>0,'msg'=>'Something went wrong, upload new picture failed.']);
        }else{
            if(Auth::user()->avatar){
                $file_path = public_path().'/user_image/'.Auth::user()->avatar;
                if(file_exists($file_path)){
                    unlink($file_path);
                }
            }
        }

        $user = User::where('id', Auth::user()->id)->first();
        $update = $user->update(['avatar' => $filename]);

        if($update){
            return response()->json(['status' => 1, 'msg' => 'Image has been cropped successfully.', 'name'=>$filename]);
        }else{
              return response()->json(['status' => 0, 'msg' => 'Something went wrong, try again later']);
        }
    }

    public function userLeave(Request $request){
        $user = User::where('id', $request->user_id)->first();
        $repeat_tasks = Task::where('creator_id', $user->id)->where('repeat_id', '<>', NULL)->get();

        foreach($repeat_tasks as $repeat){
            $repeat_task = Repeat::where('id', $repeat->repeat_id)->first();
            if($repeat_task){
                $repeat_task->delete();
            }
        }
        $user->update(['leave' => 1]);

        return back();
    }

    public function checkUserLogin(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();  

        if(!$user)
            return 'No email found';

        if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 2)) {
            return 'Too many attempts!';
        }else{
            if (!Hash::check($request->password, $user->password))
            {
                return 'Email or password is wrong';
            }
            
            return [
                'status' => 'Success',
                'user_id' => $user->id,
            ];
        }
    }

    public function export() 
    {
        return FacadesExcel::download(new UserExport, 'users.xlsx');
    }

    public function sector() 
    {
        return FacadesExcel::download(new MediaExport, 'media.xlsx');
    }
}
