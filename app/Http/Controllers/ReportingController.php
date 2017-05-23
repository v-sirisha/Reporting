<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lang;
use Schema;
use Carbon\Carbon;
use App\Http\Requests\pt_request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\platforms;
use App\Models\platform_dates;
use App\Models\country;
use App\Models\device;
use App\Models\PR;
use App\Models\PR_table;
use App\Models\io_product;
use App\Models\tags;
use App\Models\tag_index;
use App\Models\pt_raw_data;

use App\Http\Requests;

class ReportingController extends Controller
{
    protected $pr, $pt,$pt_dates;
    public function _construct(platforms $pt, platform_dates $pt_dates, PR $pr){
        $this->pt = $pt;
        $this->pt_dates = $pt_dates;
        $this->pr = $pr;
    }
    public function index(){
        $pr_miss = $this->task('PR');
        $io_miss = $this->task('io_product');
        $parent_publishers = io_product::distinct()->pluck('parent_publisher');
        $ym_managers = io_product::distinct()->pluck('ym_manager');
        $product_names = pr_table::distinct()->pluck('product_name');
        $country = $this->task('country');
        $device = $this->task('device');
        $gen_col = array('id','created_at','updated_at','tag_index_placement','tag','ad_unit','device','country','buyer');
        $columns = Schema::getColumnListing('pt_raw_data');
        array_unshift($columns, 'platform_name');
        $columns = array_merge($columns,Schema::getColumnListing('PR_table'));
        $columns = array_merge($columns,Schema::getColumnListing('device'));        
        $columns = array_merge($columns,Schema::getColumnListing('country'));
        $columns = array_merge($columns,Schema::getColumnListing('io_product'));
        $columns = array_unique(array_diff($columns, $gen_col));
        return view('reporting.index',compact('pr_miss','io_miss','parent_publishers','ym_managers','product_names','country','device','columns'));
    }
    public function getFinalData(){

        /*Platform_data table display */

        $data = platforms::paginate(100);
        foreach ($data as $key => $value) {
            $value->date = Carbon::parse($value->date)->format('Y-m-d');
        }
        return view('final-data',compact('data'));
    }

    public function addPRDetails(Request $request){
        /* Add pr data in pr_table */
        PR::firstOrCreate($request->except(['_token']));
    }
    public function storedata(pt_request $request,$platform){

        $start_date = Carbon::parse($request->start_date)->format('Y-m-d H:i:00');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d H:i:00');
        $data_exist = count(platform_dates::where('platform_name',$request->platform_name)->get());
        $data = pt_raw_data::whereBetween('date',[$start_date,$end_date])->leftjoin('tag',function($leftjoin) use($request){
            $leftjoin->on('tag.id','=','pt_raw_data.tag');
        })->where('tag.platform_name',$request->platform_name)->delete();
        if($data_exist > 0){
            platform_dates::where('platform_name',$request->platform_name)->update(['start_date'=>$start_date,'end_date'=>$end_date]);
        }
        else{
            platform_dates::firstOrCreate(["platform_name"=>$request->platform_name,"start_date"=>$start_date,"end_date"=>$end_date]);
        }
        try {
            Excel::load($request->file('excel-file'), function ($reader) use($request){

                $start_date = Carbon::parse($request->start_date)->format('Y-m-d H:i:00');
                $end_date = Carbon::parse($request->end_date)->format('Y-m-d H:i:00');
                //pt_raw_data::leftjoin('tag','tag.id','=','pt_raw_data.tag')->whereBetween('date',[$start_date,$end_date])->where('tag.platform_name',$request->platform_name)->delete();
                
                foreach ($reader->toArray() as $row) {

                    $rowdata = $row;
                    $row = null;

                    if(!is_a($rowdata['date'], 'DateTime')){
                        $rowdata['date'] = Carbon::parse($rowdata['date'])->format('Y-m-d H:i:00');
                    }
                    $row['date'] = $rowdata['date'];
                    $row['platform_name'] = $request->platform_name;

                    if(isset($rowdata['tag_id'])){
                        $row['tag_id'] = $rowdata['tag_id'];
                    }
                    else{
                        $row['tag_id'] = null;
                    }
                    if(isset($rowdata['tag_name']))    
                        $row['tag_name'] = $rowdata['tag_name'];
                    else
                        $row['tag_name'] = null;

                    if(isset($rowdata['site_name']))
                        $row['site_name'] = $rowdata['site_name'];
                    else
                        $row['site_name'] = null;

                    if(isset($rowdata['ad_unit']))
                        $row['ad_unit'] = $rowdata['ad_unit'];
                    else
                        $row['ad_unit'] = null;

                    if(isset($rowdata['device']))
                        $row['device'] = $rowdata['device'];
                    else
                        $row['device'] = "mixed";

                    if(isset($rowdata['country']))
                        $row['country'] = $rowdata['country'];
                    else
                         $row['country'] = null;

                    if(isset( $rowdata['buyer']))
                        $row['buyer'] = $rowdata['buyer'];
                    else
                         $row['buyer'] = null;

                    if(isset( $rowdata['adserver_impressions']))
                        $row['adserver_impressions'] = $rowdata['adserver_impressions'];
                    else
                         $row['adserver_impressions'] = 0;

                    if(isset($rowdata['ssp_impressions']))
                        $row['ssp_impressions'] = $rowdata['ssp_impressions'];
                    else
                         $row['ssp_impressions'] = 0;

                    if(isset($rowdata['filled_impressions']))
                        $row['filled_impressions'] = $rowdata['filled_impressions'];
                    else
                        $row['filled_impressions'] = 0;

                    if(isset($rowdata['gross_revenue']))
                        $row['gross_revenue'] = $rowdata['gross_revenue'];
                    else
                         $row['gross_revenue'] = 0.00;

                    $tag['platform_name'] = $row['platform_name'];
                    $tag['tag_id'] = $row['tag_id'];
                    $tag['tag_name'] = $row['tag_name'];
                    $tag['site_name'] = $row['site_name'];
                    if($row['tag_id'] == null)
                        $tag['tag'] = $row['tag_name'];
                    else
                        $tag['tag'] = $row['tag_id'];

                    $raw_data['date'] = $row['date'];
                    $raw_data['ad_unit'] = $row['ad_unit'];
                    $raw_data['device'] = $row['device'];
                    $raw_data['country'] = $row['country'];
                    $raw_data['buyer'] = $row['buyer'];
                    $raw_data['adserver_impressions'] = $row['adserver_impressions'];
                    $raw_data['ssp_impressions'] = $row['ssp_impressions'];
                    $raw_data['filled_impressions'] = $row['filled_impressions'];
                    $raw_data['gross_revenue'] = $row['gross_revenue'];

                    $tag_exist = tags::where('platform_name',$request->platform_name)->where('site_name',$tag['site_name'])->where('tag_id',$tag['tag_id'])->where('tag_name',$tag['tag_name'])->value('id');
                    
                    if(!$tag_exist){
                        $tag_detail = tags::firstOrCreate($tag);
                        $raw_data['tag'] = $tag_detail['id'];
                    }
                    else{
                        $raw_data['tag'] = $tag_exist;
                    }
                    //dd($raw_data);
                    pt_raw_data::firstOrCreate($raw_data);
                }
            });
        } catch (Exception $e) {
            
        }
        return redirect()->back();
    }
    public function getPRView(Request $request){
        $site_name = $request->site_name;
        $ad_unit = $request->ad_unit;
        $data = pt_raw_data::leftjoin('tag','tag.id','=','pt_raw_data.tag')
                ->leftjoin('tag_index','tag_index.tag_id','=','tag.id')
                ->leftjoin('PR_table','PR_table.tag_index_placement','=','tag_index.final_placement_name')
                ->leftjoin('io_product','io_product.final_placement_tag','=','PR_table.tag_index_placement')
                ->select('pt_raw_data.*','PR_table.io_publisher_name','PR_table.product_name','PR_table.tag_index_placement','PR_table.actual_ad_unit','io_product.*')->paginate(100);;        
        foreach ($data as $key => $value) {
            $value->date = Carbon::parse($value->date)->format('Y-m-d');
        }
        return view('pr-data',compact('data'));
    }
    public function exportToPRExcel(Request $request){        
        Excel::create('PR-Report', function($excel) {
            $excel->sheet('Sheet 1',function($sheet){
                $data = pt_raw_data::leftjoin('tag','tag.id','=','pt_raw_data.tag')
                        ->leftjoin('tag_index','tag_index.tag_id','=','tag.id')
                        ->leftjoin('PR_table','PR_table.tag_index_placement','=','tag_index.final_placement_name')
                        ->leftjoin('io_product','io_product.final_placement_tag','=','PR_table.tag_index_placement')
                        ->get();
                foreach ($data as $key => $value) {
                    $value->date = Carbon::parse($value->date)->format('Y-m-d');
                    $final[] = array(
                        $value->platform_name,
                        $value->date,
                        $value->site_name,
                        $value->tag_id,
                        $value->tag_name,
                        $value->ad_unit,
                        $value->device,
                        $value->country,
                        $value->buyer,
                        $value->adserver_impressions,
                        $value->ssp_impressions,
                        $value->filled_impressions,
                        $value->gross_revenue,
                        $value->io_publisher_name,
                        $value->product_name,
                        $value->actual_ad_unit,
                        $value->final_placement_name,
                        $value->deal_type,
                        $value->date_of_io_creation,
                        $value->publisher_manager,
                        $value->ym_manager,
                        $value->publisher_url,
                        $value->publisher_category,
                        $value->country_origin,
                        $value->language,
                        $value->business_name,
                        $value->billing_currency,
                    );
                }
                $sheet->fromArray($final, null, 'A1', false, false);
                $headings = array('Platform Name','Date', 'Site Name','Tag Id','Tag Name','Ad Unit','Device','Country','Buyer','Adserver Impressions','SSP Impressions','Filled Impressions','Gross Revenue','PP Name','Product Name','Actual Ad Unit','Final Placement name','Deal Type','Date of IO creation','Publisher Manager','YM Manager','Publisher Url','Publisher Category','Country of Origin','language','Business Name','Billing Currency');
                $sheet->prependRow(1, $headings);
            });
            
        })->export('xlsx');
    }
    public function importToDB(Request $request){
        try {
            Excel::load($request->file('file'), function ($reader) use($request){
                foreach ($reader->toArray() as $row) {
                    //dd($row);
                    switch ($request->table_name) {
                        case 'PR':
                            /* get placement tag from tag_index */

                            $data = tags::where('platform_name',$row['platform_name'])->where('site_name',$row['site_name'])
                            ->where('tag.tag_id',$row['tag_id'])->where('tag_name',$row['tag_name'])->join('tag_index','tag.id','=','tag_index.tag_id')
                            ->get(['final_placement_name']);


                            if(count($data) == 0){
                                

                                /* update tag_index with tag_id and placement_tag */

                                $tag_index = tags::where('platform_name',$row['platform_name'])->where('site_name',$row['site_name'])
                                ->where('tag_id',$row['tag_id'])->where('tag_name',$row['tag_name'])->value('tag.id');
                                
                                if($tag_index){
                                    //dd($tag_index);
                                    $tag_index_details = tag_index::firstOrCreate(['tag_id'=>$tag_index,'final_placement_name'=>$row['final_placement_name']]);

                                    $pr_data['tag_index_placement'] = $tag_index_details['final_placement_name'];
                                    $pr_data['io_publisher_name'] = $row['io_publisher_name'];
                                    $pr_data['product_name'] = $row['product_name'];
                                    $pr_data['actual_ad_unit'] = $row['actual_ad_unit'];

                                    PR_table::firstOrCreate($pr_data);
                                }
                                else{
                                    $tag['platform_name'] = $row['platform_name'];
                                    $tag['tag_id'] = $row['tag_id'];
                                    $tag['tag_name'] = $row['tag_name'];
                                    $tag['site_name'] = $row['site_name'];

                                    if($row['tag_id'] == null)
                                        $tag['tag'] = $row['tag_name'];
                                    else
                                        $tag['tag'] = $row['tag_id'];
                                    $tag_detail = tags::firstOrCreate($tag);

                                    $tag_index_details = tag_index::firstOrCreate(['tag_id'=>$tag_detail->id,'final_placement_name'=>$row['final_placement_name']]);

                                    $pr_data['tag_index_placement'] = $row['final_placement_name'];
                                    $pr_data['io_publisher_name'] = $row['io_publisher_name'];
                                    $pr_data['product_name'] = $row['product_name'];
                                    $pr_data['actual_ad_unit'] = $row['actual_ad_unit'];
                                    PR_table::firstOrCreate($pr_data);
                                }
                            }
                            else{
                                /* If placement tag exist (duplicate data) : delete- update | nothing */

                            }
                            break;
                        case 'io_product':

                            if(!is_a($row['date_of_io_creation'], 'DateTime')){
                                $row['date_of_io_creation'] = Carbon::parse($row['date_of_io_creation'])->format('Y-m-d H:i:00');
                            }
                        
                            $pr_tb_placementId = PR_table::where('tag_index_placement',$row['final_placement_tag'])->value('tag_index_placement');
                            if($pr_tb_placementId){

                                $io_product_placementId = io_product::where('final_placement_tag',$row['final_placement_tag'])->value('final_placement_tag');
                                if($io_product_placementId){
                                    io_product::where('final_placement_tag',$row['final_placement_tag'])->update($row);
                                }
                                else{
                                    io_product::firstOrCreate($row);
                                }
                            }
                            else{
                                /* final placemet tag not exist in pr_table new entry in io_excel */
                            }
                            break;
                        case 'country':
                            $country = country::where('country_name',$row['country_name'])->value('id');
                            if(!$country){
                                country::firstOrCreate(["country_name"=>$row['country_name'],"analytics_country_group"=>$row['analytics_country_group'],"deal_country_group"=>$row['deal_country_group']]);
                            }
                            else{
                                country::where('country_name',$row['country_name'])->update(["analytics_country_group" => $row['analytics_country_group'],"deal_country_group" => $row['deal_country_group']]);
                            }
                            
                            break;
                        case 'device':
                            $device = device::where('device_name',$row['device_name'])->value('id');
                            if(!$device){
                                device::firstOrCreate(["device_name"=>$row['device_name'],"device_group"=>$row['device_group']]);
                            }
                            else{
                                device::where('device_name',$row['device_name'])->update(["device_group"=>$row['device_group']]);
                            }
                            
                            break;
                        default:
                            break;
                    }
                    
                }
            });
        }
        catch(Exception $e){

        }
    }
    public function task($table="PR"){
        $pr = null;
        $io = null;
        if($table == "PR"){

            $ids = tags::pluck('id');
            $pt_ids = pt_raw_data::distinct()->pluck('tag')->toArray();
            $tagIds = tag_index::distinct()->pluck('tag_id')->toArray();

            $pt_empty = array_diff($tagIds, $pt_ids);
            $msg = "";
            $miss_pr = null;
            if(count($pt_empty) < 0){
                $msg = "Please update platforms data ";
            }
            $miss_ids = array_diff($pt_ids, $tagIds) ; /* placement tag not exist in tag_index */

            $miss_values = pr_table::where('io_publisher_name', '=', '')->orWhereNull('io_publisher_name')
                        ->orWhere('product_name', '=', '')->orWhereNull('product_name')
                        ->orWhere('actual_ad_unit', '=', '')->orWhereNull('actual_ad_unit')
                        ->pluck('tag_index_placement'); /* exist in pr_table but empty values */

            $empty_id_arr = tag_index::whereIn('final_placement_name',$miss_values)->get(['tag_id'])->toArray();
            $total_ids = array_merge($miss_ids,$empty_id_arr);
            if(count($total_ids) > 0){
                $miss_pr = tags::whereIn('tag.id',$total_ids)
                            ->leftjoin('tag_index','tag.id','=','tag_index.tag_id')
                            ->leftjoin('PR_table','tag_index.final_placement_name','=','PR_table.tag_index_placement')
                            ->get(['tag.*','tag_index.final_placement_name','PR_table.*']);
            }
            return $miss_pr;
        }
        else if($table == "io_product") {
            $tag_index_placement = PR_table::distinct()->pluck('tag_index_placement')->toArray();
            $final_placement_tag = io_product::distinct()->pluck('final_placement_tag')->toArray();
            $miss_values = io_product::where('deal_type', '=', '')->orWhereNull('deal_type')
                        ->orWhere('parent_publisher', '=', '')->orWhereNull('parent_publisher')
                        ->orWhere('date_of_io_creation', '=', '')->orWhereNull('date_of_io_creation')
                        ->orWhere('parent_publisher', '=', '')->orWhereNull('parent_publisher')
                        ->orWhere('ym_manager', '=', '')->orWhereNull('ym_manager')
                        ->orWhere('publisher_url', '=', '')->orWhereNull('publisher_url')
                        ->orWhere('publisher_category', '=', '')->orWhereNull('publisher_category')
                        ->orWhere('country_origin', '=', '')->orWhereNull('country_origin')
                        ->orWhere('language', '=', '')->orWhereNull('language')
                        ->orWhere('business_name', '=', '')->orWhereNull('business_name')
                        ->orWhere('billing_currency', '=', '')->orWhereNull('billing_currency')
                        ->pluck('final_placement_tag'); /* placement tag exist in io_product null values */
            $miss_placement = array_diff($tag_index_placement, $final_placement_tag); /* exist in pr_table but not in io_product */
            
            /* Merge miss_placement and miss values and pass to loop */
            if(count($miss_values) > 0){
                $miss_io = io_product::whereIn('final_placement_tag',$miss_values)->get();
            }
            return $miss_io;
        }
        else if($table == "country"){
            $countries = country::where('analytics_country_group','=', '')->orWhereNull('analytics_country_group')
                        ->orWhere('deal_country_group', '=', '')->orWhereNull('deal_country_group')
                        ->distinct()->pluck('country_name');
            $miss_countries = country::whereIn('country_name',$countries)->get();
            //dd($miss_countries);
            return $miss_countries;
        }
        else if($table == 'device'){
            $device = device::where('device_group','=', '')->orWhereNull('device_group')
                        ->distinct()->pluck('device_name');
            $miss_device = device::whereIn('device_name',$device)->get();
            return $miss_device;
        }
    }
    public function download_miss_data_excel($type){
       // dd($type);
        switch ($type) {
            case 'PR':
                Excel::create('PR-data', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){

                        $miss_pr = $this->task('PR');
                        
                        foreach ($miss_pr as $key => $value) {

                            $final[] = array(
                                $value->platform_name,
                                $value->site_name,
                                $value->tag_id,
                                $value->tag_name,
                                $value->io_publisher_name,
                                $value->product_name,
                                $value->actual_ad_unit,
                                $value->final_placement_name,
                            );
                        }
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Platform Name', 'Site Name','Tag Id','Tag Name','IO Publisher Name','Product Name','Actual Ad Unit Size','final placement name');
                        $sheet->prependRow(1, $headings);
                    });
                    
                })->export('xlsx');
                return view('/');
                break;
            case 'io_product':
               Excel::create('io-data', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $miss_placement = $this->task('io_product');
                        foreach ($miss_placement as $key => $value) {
                            $final[] = array(
                                $value->final_placement_tag,
                                $value->deal_type,
                                $value->date_of_io_creation,
                                $value->publisher_manager,
                                $value->ym_manager,
                                $value->publisher_url,
                                $value->publisher_category,
                                $value->country_origin,
                                $value->language,
                                $value->business_name,
                                $value->billing_currency
                            );
                        }
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Final Placement name','Deal Type','Date of IO creation','Publisher Manager','YM Manager','Publisher Url','Publisher Category','Country of Origin','language','Business Name','Billing Currency');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            case 'country':
                Excel::create('country', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $miss = $this->task('country');
                        foreach ($miss as $key => $value) {
                            $final[] = array(
                                $value->country_name,
                                $value->analytics_country_group,
                                $value->deal_country_group
                            );
                        }
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Country Name','Analytics Country Group','Deal Country Group');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            case 'device':
                Excel::create('device', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $miss = $this->task('device');
                        foreach ($miss as $key => $value) {
                            $final[] = array(
                                $value->device_name,
                                $value->device_group
                            );
                        }
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Device Name','Device Group');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            default:
                # code...
                break;
        }
    }
    public function updateon_screen($type){
        $data = $this->task($type);
        //dd($data);
        return view('reporting.update_screen',compact('data','type'));
    }
    public function getEndDate($pt_name){
        $date = platform_dates::where('platform_name',$pt_name)->value('end_date');
        if($date)
            $date = Carbon::parse($date)->format('d M Y');
        return $date;
    }
    public function defaultTemplates($type){
        switch ($type) {
            case 'tags':                
                Excel::create('tags', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $final[] = array('','','','','','','','','','','','');
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Date','Site Name','Tag Id','Tag Name','Ad unit Size','Country','Device','Buyer','AdServer Impressions','SSP Impressions','Filled Impressions','Gross Revenue');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');                
                break;
            case 'PR':
                Excel::create('Io Publisher', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $final[] = array('','','','');
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Final Placement Tag','Io Publisher Name','Product Name','Actual AdUnit');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            case 'io_product':
                Excel::create('Publisher Manager', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $final[] = array('','','','','','','','','','','','');
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Final Placement name','Deal Type','Date of IO creation','Publisher Manager','YM Manager','Publisher Url','Publisher Category','Country of Origin','language','Business Name','Billing Currency');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            case 'country':
                Excel::create('Country', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $final[] = array('','','');
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Country Name','Analytics Country Group','Deal Country Group');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            case 'device':
                Excel::create('Device', function($excel) {
                    $excel->sheet('Sheet 1',function($sheet){
                        $final[] = array('','');
                        $sheet->fromArray($final, null, 'A1', false, false);
                        $headings = array('Device Name','Device Group');
                        $sheet->prependRow(1, $headings);
                    });
                })->export('xlsx');
                break;
            default:
                # code...
                break;
        }
    }
    public function getFinalPR_Report(Request $request){
        //dd($request->all());
        if(isset($request->control_1)){
            $columns = $request->control_1;
        }
        else{
            $columns = null;
        }

        $start_date = Carbon::parse($request->start_date)->format('Y-m-d H:i:00');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d H:i:00');

        $data = pt_raw_data::whereBetween('date',[$start_date,$end_date])
            ->leftjoin('tag','tag.id','=','pt_raw_data.tag')
            ->leftjoin('tag_index','tag_index.tag_id','=','tag.id')
            ->leftjoin('PR_table','PR_table.tag_index_placement','=','tag_index.final_placement_name')
            ->leftjoin('io_product','io_product.final_placement_tag','=','PR_table.tag_index_placement')
            ->leftjoin('device','pt_raw_data.device','=','device.device_name')
            ->leftjoin('country','pt_raw_data.country','=','country.country_name')
            ->get($columns);
        //dd($data);
        if(isset($request->product_name)){
            $data = $data->where('product_name',$request->product_name);
        }
        if(isset($request->publisher_manager)){
            $data = $data->where('publisher_manager',$request->publisher_manager);
        }
        if(isset($request->ym_manager)){
            $data = $data->where('ym_manager',$request->ym_manager);
        }
        if(count($data) > 0){
            Excel::create('Io Publisher', function($excel) use($data,$columns){
                $excel->sheet('Sheet 1',function($sheet) use($data,$columns){
                    $final = array();
                    foreach ($data as $key => $value) {
                        $final2 = array();
                        for ($i=0; $i < count($columns); $i++) { 
                           array_push($final2, $value->$columns[$i]);
                        }
                        $final[$key] = $final2;
                    }
                    $sheet->fromArray($final, null, 'A1', false, false);
                    $sheet->prependRow(1, $columns);
                });
            })->export('xlsx');
        }
        else{
            $msg = 'empty';
            return $msg;
        } 
    }
    public function onScreenAction(Request $request){
        $type = $request->type;
        //return ($request->all());
        switch ($type) {
            case 'PR':
                $platform_name = $request->platform_name;
                $site_name =  $request->site_name;
                $tag_id =  $request->tag_id;
                $tag_name =  $request->tag_name;
                $id = tags::where('platform_name',$platform_name)
                        ->where('site_name',$site_name)
                        ->where('tag_id',$tag_id)
                        ->where('tag_name',$tag_name)->value('id');
                $tag_index_id = tag_index::where('tag_id',$id)->get();
                
                if(count($tag_index_id)>0){
                    $success = PR_table::where('tag_index_placement',$request->final_placement_name)
                            ->update(['io_publisher_name'=>$request->io_publisher_name,'product_name'=>$request->product_name,'actual_ad_unit'=>$request->actual_ad_unit]);
                }
                else{

                    $success = PR_table::firstOrCreate(['tag_index_placement'=>$request->final_placement_name,'io_publisher_name'=>$request->io_publisher_name,'product_name'=>$request->product_name,'actual_ad_unit'=>$request->actual_ad_unit]);
                    if($success){
                        tag_index::firstOrCreate(['tag_id'=>$id,'final_placement_name'=>$request->final_placement_name]);
                    }
                    
                }
                return $success;
                break;
            case 'io_product':
                $data =  $request->except(['type']);
                $success = io_product::where('final_placement_tag',$request->final_placement_tag)
                            ->update($data);
                return ($success);
                break;
            case 'country':
                $data =  $request->except(['type']);
                $success = country::where('country_name',$request->country_name)
                            ->update($data);
                return $success;
                break;
            case 'device':
                $data =  $request->except(['type']);
                $success = device::where('device_name',$request->device_name)
                            ->update($data);
                return $success;
                break;
            default:
                # code...
                break;
        }
    }
}
