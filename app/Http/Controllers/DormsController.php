<?php

namespace App\Http\Controllers;

use App\Dbstat;
use App\Movement;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Dorm;
use App\Room;
use App\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Excel;

class DormsController extends Controller
{
    public $message = '';

    public function getDormsTemplate(Request $request)
    {
        if ($request->isMethod('get')) {
            $user = Auth::user();
            $dorms = Dorm::orderBy('created_at', 'desc')->paginate(20);
            $can_insert = true;
            if (is_null(Dorm::first())) {
                $can_insert = false;
            }
            return view('dorms/dorms-template', ['user' => $user, 'dorms' => $dorms, 'can_insert' => $can_insert]);
        } elseif ($request->isMethod('post')) {
            try {
                $dorm = Dorm::where('code', '=', $request->code)->first();
                if (!is_null($dorm)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aceast cămin există deja.',
                    ]);
                }

                $dorm = new Dorm();
                $dorm->name = $request->name;
                $dorm->code = $request->code;
                $dorm->description = $request->description;

                $dorm->save();
                return response()->json([
                    'success' => true,
                    'message' => 'A fost introdus căminul ' . $dorm->name,
                ]);
            } catch (\Exception $e) {
                $user = Auth::user();
                MessageController::sendMessageToAdmin($user->id, $e, 'EROARE');
                return response()->json([
                    'success' => false,
                    'message' => 'Am întâmpinat o problemă. Vă rugăm să ne contactați telefonic!',
                ]);
            }
        }
    }

    public function uploadRoomsFile(Request $request)
    {
        $file = $request->file('roomTemplate');
        $extension = $request->file('roomTemplate')->getClientOriginalExtension();
        $name = 'Rooms' . time() . '.' . $extension;
        Storage::disk('files')->put($name, File::get($file));
        $path = public_path() . '/files/' . $name;
        Excel::load($path, function ($reader) {
            $reader->each(function ($row) {
                $result = self::createRoom($row);
                if ($result['success']) {
                    $this->message .= "\nAu fost adăugate camerele de la " . $row->camera_de_la . " pană la " . $row->camera_pana_la . " a căminului cu codul " . $row->cod_camin;
                } else {
                    $this->message .= "\n" . $result['message'];
                }

            });

        });
        $current_user = Auth::user();
        MessageController::sendMessageFromAdmin($current_user->id, $this->message, 'Adăugare camere');
        return redirect('/dorms');
    }

    public static function createRoom($room)
    {
        $dorm = Dorm::where('code', '=', trim($room->cod_camin))->first();
        if (is_null($dorm)) {
            return ['success' => false, 'message' => 'Nu există cămin cu codul ' . $room->cod_camin];
        }
        $institution = Institution::where('code', '=', $room->cod_facultate)->first();
        if (is_null($institution)) {
            return ['success' => false, 'message' => 'Nu există instituție cu codul ' . $room->cod_facultate];
        }

        $room->camera_de_la = intval($room->camera_de_la);
        $room->camera_pana_la = intval($room->camera_pana_la);
        for ($i = $room->camera_de_la; $i <= $room->camera_pana_la; $i++) {
            $new_room = new Room();
            $new_room->dorm_code = $room->cod_camin;
            $new_room->room_number = $i;
            $new_room->capacity = $room->capacitate;
            $new_room->institution_code = $room->cod_facultate;

            $new_room->save();
        }
        return ['success' => true];

    }

    public static function getAvailableDorms()
    {
        $dorms = Dorm::all();
        $available_codes = [];
        foreach ($dorms as $dorm) {
            $room = Room::where('dorm_code', '=', $dorm->code)->where('institution_code', '=', 'INFO')->whereRaw('occupation < capacity')->first();
            if (!is_null($room)) {
                $available_codes[] = $dorm->code;
            }
        }

        return $available_codes;
    }

    public function selectDorm(Request $request)
    {
        $user = Auth::user();
        if ($request->isMethod('get')) {
            $has_been_accepted = false;
            $data = [];
            $main_movement = Movement::where('user_id', '=', $user->id)->where('has_been_parsed', '=', true)->where('acceptance', '=', true)->first();

            if (!is_null($main_movement)) {
                $has_been_accepted = true;
                $base_room = Room::where('id','=', $main_movement->room_id)->first();
                $data['main_dorm'] = Dorm::where('code', '=', $base_room->dorm_code)->first();
            }

            $codes = [];
            $dorm_codes = DB::table('rooms')->select(DB::raw('distinct dorm_code'))->where('institution_code', '=', $user->contact->institution_code)->get()->toArray();
            foreach ($dorm_codes as $dorm_code) {
                $codes[] = $dorm_code->dorm_code;
            }
            $dorms = Dorm::whereIn('code', $codes)->paginate(20);
            $can_apply = false;
            $can_apply_dorm_codes = DB::table('rooms')->select(DB::raw('distinct dorm_code'))->where('institution_code', '=', $user->contact->institution_code)->whereRaw('occupation < capacity')->get()->toArray();
            if (!is_null($can_apply_dorm_codes) && is_array($can_apply_dorm_codes) && count($can_apply_dorm_codes) > 0) {
                $can_apply = true;
            }
            $can_apply_codes = [];
            foreach ($can_apply_dorm_codes as $dorm_code) {
                $can_apply_codes[] = $dorm_code->dorm_code;
            }
            $dorms_can_apply = Dorm::whereIn('code', $can_apply_codes)->get();

            $has_applied = false;
            $has_applied_quesry = Movement::where('user_id', '=', $user->id)->where('has_been_parsed', '=', false)->orderBy('created_at', 'desc')->first();
            if (!is_null($has_applied_quesry)) {
                $has_applied = true;
            }

            $dbstat = Dbstat::orderBy('created_at', 'desc')->first();
            $data['db_stat'] = $dbstat;
            $data['has_applied'] = $has_applied;
            $data['user'] = $user;
            $data['has_been_accepted'] = $has_been_accepted;
            $data['dorms'] = $dorms;
            $data['can_apply'] = $can_apply;
            $data['dorms_can_apply'] = $dorms_can_apply;
            return view('dorms/select-dorm', $data);
        } elseif ($request->isMethod('post')) {
            $all_dorms = explode(',', $request->dorm);
            if (count($all_dorms) == 1) {
                $dorm = Dorm::where('code', '=', $request->dorm)->first();
                if (is_null($dorm)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nu există cămin cu acest cod',
                    ]);
                }
                $code = $request->dorm;
            } else {
                foreach ($all_dorms as $d) {
                    $dorm = Dorm::where('code', '=', $d)->first();
                    if (is_null($dorm)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Nu există cămin cu acest cod',
                        ]);
                    }
                }
                $code = $request->dorm;
            }

            $movement = new Movement();
            $movement->user_id = $user->id;
            $movement->institution_code = $user->contact->institution_code;
            $movement->dorm_code = $code;
            $movement->acceptance = false;
            $movement->has_been_parsed = false;
            $movement->sex = $user->contact->sex;
            $movement->grade = $user->contact->grade;
            $movement->room_id = -1;
            $movement->save();
            return response()->json([
                'success' => true,
            ]);
        }
    }

    public function startSort(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'A început sortarea studenților.'
        ]);
    }

    public static function beginSort()
    {
        $last_db_stat = Dbstat::orderBy('created_at', 'desc')->first();
        $stat = new Dbstat();
        $stat->start = $last_db_stat->start;
        $stat->end = $last_db_stat->end;
        $stat->can_operate = false;
        $stat->algorithm = $last_db_stat->algorithm;
        $stat->save();

        if($last_db_stat->algorithm == 'preference'){
            self::sortByPreference();
        }elseif ($last_db_stat->algorithm == 'cascade'){
            self::sortCascade();
        }

        $last_db_stat = Dbstat::orderBy('created_at', 'desc')->first();
        $stat = new Dbstat();
        $stat->start = $last_db_stat->start;
        $stat->end = $last_db_stat->end;
        $stat->can_operate = true;
        $stat->algorithm = $last_db_stat->algorithm;
        $stat->save();

    }

    public static function sortCascade()
    {
        $movements = Movement::where('has_been_parsed', '=', false)->where('acceptance', '=', false)->orderBy('grade', 'desc')->cursor();
        foreach ($movements as $movement) {
            if(is_numeric($movement->room_id) && $movement->room_id != -1){
                continue;
            }
            $dorm_codes = explode(',', $movement->dorm_code);
            $can_be_accepted = false;
            foreach ($dorm_codes as $dorm_code) {
                $available_room = self::getAvailableRoom($dorm_code,$movement->institution_code, $movement->sex);
                if(is_null($available_room)){
                    continue;
                }
                $can_be_accepted = true;
                break;
            }

            if($can_be_accepted){
                $movement->room_id = $available_room->id;
                $movement->has_been_parsed = true;
                $movement->acceptance = true;
                $movement->save();
                self::updateRoom($available_room);
            }else{
                $movement->has_been_parsed = true;
                $movement->acceptance = false;
                $movement->save();
            }
        }
    }

    public static function getAvailableRoom($code, $institution_code, $sex)
    {

        $room_counter = Room::where('dorm_code', '=', $code)->where('institution_code','=', $institution_code)->whereRaw('occupation < capacity')->count();
        if(is_null($room_counter) || $room_counter == 0){
            return null;
        }
        $rooms = Room::where('dorm_code', '=', $code)->where('institution_code','=', $institution_code)->whereRaw('occupation < capacity')->cursor();
        foreach ($rooms as $room){
            if($room->occupation == 0){
                return $room;
            }elseif ($room->occupation < $room->capacity){
                $best_movement = Movement::where('room_id', '=', $room->id)->where('has_been_parsed', '=', true)
                    ->where('acceptance', '=', true)->orderBy('grade', 'desc')->first();
                if(is_null($best_movement)){
                    continue;
                }
                if($sex == $best_movement->sex){
                    return $room;
                }
            }
        }


        return null;
    }

    public static function updateRoom($room)
    {
        $room->occupation = $room->occupation + 1;
        $room->save();
        return;
    }

    public static function sortByPreference()
    {
        $institutions = Institution::orderBy('created_at', 'asc')->get();
        foreach ($institutions as $institution) {
            $rooms = Room::where('institution_code', '=', $institution->code)->whereRaw('occupation < capacity')->cursor();
            foreach ($rooms as $room) {
                if ($room->occupation == 0) {
                    $highest_movement = Movement::where('dorm_code', '=', $room->dorm_code)->where('institution_code', '=', $institution->code)->where('has_been_parsed', '=', false)->orderBy('grade', 'desc')->first();
                    if (!is_null($highest_movement)) {
                        $movements = Movement::where('dorm_code', '=', $room->dorm_code)->where('institution_code', '=', $institution->code)->where('has_been_parsed', '=', false)->where('sex', '=', $highest_movement->sex)->orderBy('grade', 'desc')->limit($room->capacity)->get();
                        $occupation = $room->occupation;
                        foreach ($movements as $movement) {
                            $movement->has_been_parsed = true;
                            $movement->acceptance = true;
                            $movement->room_id = $room->id;
                            $movement->save();
                            $occupation++;
                        }
                        $room->occupation = $occupation;
                        $room->save();
                    }
                } else {
                    $highest_movement = Movement::where('room_id', '=', $room->id)->where('dorm_code', '=', $room->dorm_code)->where('institution_code', '=', $institution->code)->where('has_been_parsed', '=', true)->orderBy('grade', 'desc')->first();
                    if (!is_null($highest_movement)) {
                        $limit = $room->capacity - $room->occupation;
                        $movements = Movement::where('dorm_code', '=', $room->dorm_code)->where('institution_code', '=', $institution->code)->where('has_been_parsed', '=', false)->where('sex', '=', $highest_movement->sex)->orderBy('grade', 'desc')->limit($limit)->get();
                        if (!is_null($movements)) {
                            foreach ($movements as $movement) {
                                $movement->has_been_parsed = true;
                                $movement->acceptance = true;
                                $movement->room_id = $room->id;
                                $movement->save();
                                $room->occupation = $room->occupation + 1;
                            }
                            $room->save();
                        }
                    }
                }
            }
        }

        $rejected_movements = Movement::where('has_been_parsed', '=', false)->orderBy('created_at', 'desc')->cursor();
        if (!is_null($rejected_movements)) {
            foreach ($rejected_movements as $rejected_movement) {
                $rejected_movement->has_been_parsed = true;
                $rejected_movement->save();
            }
        }
    }

    public function stopDorminator(Request $request)
    {
        $last_stat = Dbstat::orderby('created_at', 'desc')->first();
        $stat = new Dbstat();
        $stat->start = true;
        $stat->end = true;
        $stat->can_operate = false;
        $stat->algorithm = $last_stat->algorithm;
        $stat->save();

        return response()->json([
            'success' => true,
            'message' => 'Toate procesele de cazare sunt din acest moment oprite.',
        ]);
    }

    public function startDorminator(Request $request)
    {
        $last_stat = Dbstat::orderby('created_at', 'desc')->first();
        $stat = new Dbstat();
        $stat->start = true;
        $stat->end = $last_stat->end;
        $stat->can_operate = true;
        $stat->algorithm = $last_stat->algorithm;
        $stat->save();

        return response()->json([
            'success' => true,
            'message' => 'Toate procesele de cazare sunt din acest moment pornite.',
        ]);
    }

    public function selectAlgorithm(Request $request)
    {
        $algortihm = $request->algorithm_type;

        $last_stat = Dbstat::orderby('created_at', 'desc')->first();
        $stat = new Dbstat();
        $stat->start = $last_stat->start;
        $stat->end = $last_stat->end;
        $stat->can_operate = $last_stat->can_operate;
        if($algortihm == 'cascade'){
            $stat->algorithm = 'cascade';
        }elseif ($algortihm == 'preference'){
            $stat->algorithm = 'preference';
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Tipul de algoritm nu există',
            ]);
        }

        $stat->save();

        return response()->json([
            'success' => true,
            'message' => 'Tipul de algoritm a fost schimbat',
        ]);
    }

    public function allocatedDorms(Request $request)
    {
        $rows = self::getAllocatedStudens();
        Excel::create('Studenți', function($excel) use($rows) {

            $excel->sheet('Studenți', function($sheet) use($rows) {
                $sheet->fromArray($rows);
            });

        })->download('xls');
    }

    public static function getAllocatedStudens()
    {
        $rows = [];
        $rows[] = ['Nr. Crt.','Numele Studentului', 'Nota de concurs', 'Instituția', 'Caminul', 'Cameră', 'Telefon', 'Email'];

        $movements = Movement::where('acceptance', '=',true)->where('room_id','<>', -1)->orderBy('grade', 'desc')->cursor();
        $index = 1;
        foreach ($movements as $movement){
            $data = [];
            $data[] = $index;
            $student = User::where('id', '=', $movement->user_id)->first();
            $data[] = $student->contact->name;
            $data[] = $movement->grade;

            $institution = Institution::where('code', '=', $movement->institution_code)->first();
            $data[] = $institution->name;
            $room = Room::where('id', '=', $movement->room_id)->first();

            $dorm = Dorm::where('code', '=', $room->dorm_code)->first();
            $data[] = $dorm->name;
            $data[] = $room->room_number;

            $data[] = $student->contact->phone;
            $data[] = $student->email;
            $index = $index + 1;
            $rows[] = $data;
        }
        return $rows;
    }
}
