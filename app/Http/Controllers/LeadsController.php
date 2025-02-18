<?php
namespace App\Http\Controllers;

use App\Exports\LeadsExport;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Salesman;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $inquiry = Lead::query()->select('leads.*', 'vehicle.name AS vehicleName', 'salesman.name AS salesmanName', 'lead_sources.name AS leadSourceName')
                ->leftJoin('vehicle', 'vehicle.id', '=', 'leads.vehicle')
                ->leftJoin('lead_sources', 'lead_sources.id', '=', 'leads.lead_source')
                ->leftJoin('salesman', 'salesman.id', '=', 'leads.salesman');

            if (! empty($request->startDate) && ! empty($request->endDate)) {
                $inquiry = $inquiry->whereBetween(DB::raw('DATE(leads.created_at)'), [$request->startDate, $request->endDate]);
            }

            return DataTables::of($inquiry)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('leads.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a>';
                })
                ->make(true);

        }
        return view('leads-list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicle    = Vehicle::pluck('name', 'id');
        $salesman   = Salesman::pluck('name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');
        return view('add-update-leads')->with(compact('leadSource', 'vehicle', 'salesman', 'leadSource'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $salesmanName   = '';
        $salesmanMobile = '';

        $nameQuery = Salesman::find($request->salesman);
        if ($nameQuery) {
            $salesmanName   = $nameQuery->name;
            $salesmanMobile = $nameQuery->mobile;
        }

        $lead = Lead::create($request->all());
        if ($lead) {
            $message = "Hi " . $request->name . "\n \n";
            $message .= "Thank you for showing your interest in Ampere Electric Vehicles.\n \n";
            $message .= "My name is " . $salesmanName . " and I will be your companion along this electrifying journey \n \n";
            $message .= "Warm Regards\n";
            $message .= $salesmanName . "\n";
            $message .= $salesmanMobile;

            $pdfUrl = '';
            if ($request->vehicle == '1') { // Nexus
                $pdfUrl = 'https://chiragautomotive.com/amper/assets/pdf/Ampere_Nexus.pdf';
            } else if ($request->vehicle == '2') { // Magnus
                $pdfUrl = 'https://chiragautomotive.com/amper/assets/pdf/Ampere_Magnus_Neo.pdf';
            } else if ($request->vehicle == '3') { // Reo
                $pdfUrl = 'https://chiragautomotive.com/amper/assets/pdf/Ampere_Reo_LI.pdf';
            }

            $this->sendWhatsAppMessageWithFile($request->mobile, $message, $pdfUrl);
        }
        return redirect()->route('leads.index')->with('success', 'Lead added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lead       = Lead::find($id);
        $leadSource = $this->leadSource;
        $vehicle    = Vehicle::pluck('name', 'id');
        $salesman   = Salesman::pluck('name', 'id');
        $leadSource = LeadSource::pluck('name', 'id');
        return view('add-update-leads')->with(compact('lead', 'leadSource', 'vehicle', 'salesman', 'leadSource'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lead = Lead::find($id);
        $lead->update($request->all());
        return redirect()->route('leads.index')->with('success', 'Lead updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addVehicle(Request $request)
    {
        $name = $request->name;
        Vehicle::create(['name' => $name]);
        return response()->json(['success' => 'Vehicle added successfully!']);
    }

    public function addSalesman(Request $request)
    {
        $name = $request->name;
        Salesman::create(['name' => $name]);
        return response()->json(['success' => 'Salesman added successfully!']);
    }

    public function addLeadSource(Request $request)
    {
        $name = $request->name;
        LeadSource::create(['name' => $name]);
        return response()->json(['success' => 'Lead Source added successfully!']);
    }

    public function vehicleDetails()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }

    public function salesmanDetails()
    {
        $vehicles = Salesman::all();
        return response()->json($vehicles);
    }

    public function leadSourceDetails()
    {
        $data = LeadSource::all();
        return response()->json($data);
    }

    public function export(Request $request)
    {
        try {
            $exportStartDate = $request->input('exportStartDate');
            $exportEndDate   = $request->input('exportEndDate');

            return Excel::download(new LeadsExport($exportStartDate, $exportEndDate), 'leads.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
