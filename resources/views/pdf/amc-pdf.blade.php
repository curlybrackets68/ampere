<!DOCTYPE html>
<html>

<head>
    <title>AMC Vehicle Service Contract</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .section {
            background-color: #8eb4e3;
            padding: 5px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 5px;
        }

        .serviceTd,
        .serviceTh {
            padding: 3px;
            border: 1px solid #000
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .contract-dates {
            float: right;
            text-align: right;
        }

        .terms li {
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <header>
        <table>
            <tr>
                <td>
                    @if ($amc->vehicle_master_id == '4' || $amc->vehicle_master_id == '5' || $amc->vehicle_master_id == '6')
                        @if (env('SERVER_MODE') == 'live' || env('SERVER_MODE') == 'test')
                            <img src="{{ url('assets/assets/img/new.png') }}"
                                style="width: 100% !important; margin-top: -30px;">
                        @else
                            <img src="{{ public_path('assets/assets/img/new.png') }}"
                                style="width: 100% !important; margin-top: -30px;">
                        @endif
                    @else
                        @if (env('SERVER_MODE') == 'live' || env('SERVER_MODE') == 'test')
                            <img src="{{ url('assets/assets/img/pdf-header2.png') }}"
                                style="width: 100% !important; margin-top: -30px;">
                        @else
                            <img src="{{ public_path('assets/assets/img/pdf-header2.png') }}"
                                style="width: 100% !important; margin-top: -30px;">
                        @endif
                    @endif

                </td>
            </tr>
        </table>
    </header>
    <div class="title">AMC<br>VEHICLE SERVICE CONTRACT</div>

    <div class="section">Vehicle Details</div>
    <table>
        <tr>
            <td>Vehicle Category: {{ $amc->vehicle_type_name }}</td>
            <td class="contract-dates">
                <strong>Contract Start Date:</strong> {{ $amc->display_amc_start_date }}
            </td>
        </tr>
        <tr>
            <td>Vehicle Make & Model: {{ $amc->vehicle_name }}</td>
            <td class="contract-dates">
                <span style="color: red;"><strong>Contract End Date:</strong>
                    {{ $amc->display_amc_end_date }}</span>
            </td>
        </tr>
        <tr>
            <td>Chassis Number: {{ $amc->chassis_number }}</td>
            <td class="contract-dates"><strong>AMC Start KM: </strong>{{ $amc->amc_start_km }}</td>
        </tr>
    </table>

    <div class="section">Customer Details</div>
    <table>
        <tr>
            <td style="width: 60%">Name: <strong>{{ $amc->customer_name }}</strong></td>
            <td style="float: right; text-align: right; width: 40%">Mobile Number:
                <strong>{{ $amc->contact_number }}</strong>
            </td>
        </tr>
        {{-- <tr>
            <td colspan="2">Address: <strong>{{ $amc->contact_address }}</strong></td>
        </tr> --}}
    </table>

    <div class="section">Service Details</div>

    <table style="text-align: center;">
        <tr>
            <th colspan="6" style="text-align: center;">
                <div style="display: inline-block;">Package: <strong>{{ $amc->amc_package_type_name }}</strong></div>
            </th>
        </tr>
        <tr>
            <th class="serviceTh" style="width: 10%;">Sr.No</th>
            <th class="serviceTh" style="width: 20%;">Service Date</th>
            <th class="serviceTh" style="width: 20%;">Service Type</th>
            <th class="serviceTh" style="width: 10%;">Service KM</th>
            <th class="serviceTh" style="width: 50%;">Service Remark</th>
            <th class="serviceTh" style="width: 10%;">Status</th>
        </tr>
        @foreach ($amc->services as $index => $service)
            <tr>
                <td class="serviceTd">{{ $index + 1 }}</td>
                <td class="serviceTd">{{ $service['display_service_date'] }}</td>
                <td class="serviceTd">{{ $service['service_type_text'] ?? '' }}</td>
                <td class="serviceTd">{{ $service['service_km'] ?? 0 }}</td>
                <td class="serviceTd">{{ $service['service_remark'] ?? '' }}</td>
                <td class="serviceTd">{{ $service['status_name'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>

    <div class="section" style="margin-top: 5px;">Terms & Conditions</div>
    <ol class="terms">
        @if ($amc->vehicle_master_id == '4' || $amc->vehicle_master_id == '5' || $amc->vehicle_master_id == '6')
            <li>This contract includes <strong>3 Free services <strong>which can be availed any time within the contract
                        duration.
            </li>
            <li>The contract will expire on the End Date mentioned above, irrespective of the number of services
                availed.

            </li>
            <li> If all 3 services are availed before the contract end date, the contract shall terminate automatically
                upon the completion of
                last service.
            </li>
            <li>Any unutilized services will lapse after the expiry date
            </li>
            <li>The cost of spare parts, additional labour, and consumables is not covered under this contract and will
                be charged
                separately as per actual.

            </li>
            <li> Services under this contract must be availed only at authorized service centers (as applicable).
            </li>
            <li><span style="background-color: yellow"><strong> Service will be done on
                        appointment basis only.</span></strong></li>
        @else
            <li>This contract includes<strong> {{ $amc->no_of_service }} paid services </strong>which can be availed
                <strong>any time within the contract duration. </strong>
            </li>
            <li><strong>The contract will expire</strong> on the <strong>End Date mentioned above, </strong>irrespective
                of the number of services availed.
            </li>
            <li>If all {{ $amc->no_of_service }} services are availed before the contract end date, <strong>the
                    contract
                    shall terminate automatically</strong> upon the completion of last service.</li>
            <li><strong>Any unutilized services will lapse </strong>after the expiry date</li>
            <li>The cost of <strong>spare parts, additional labour, and consumables </strong>is<strong> not covered
                </strong>under this contract and will be
                <strong>charged separately </strong>as per actuals.
            </li>
            <li>Services under this contract must be availed only at authorized service centers (as applicable).</li>
            <li><span style="background-color: yellow"><strong>Service will be done on appointment basis
                        only.</span></strong></li>
            <li> Flat 10% discount on all spare parts, except critical electrical items</li>
        @endif

    </ol>

    <div class="section">Payment Details</div>
    <table>
        <tr>
            <td style="text-align: left;">Payment Type: <strong> {{ $amc->payment_type_name }} </strong></td>
            <td style="text-align: right;">
                AMC Amount: <strong>{{ $amc->amc_basic_price }} </strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">Transaction No: <strong>{{ $amc->transaction_details }}</strong></td>
        </tr>
    </table>

    <hr>

    <footer>
        <table width="100%">
            <tr>
                <td width="70%" style="vertical-align: top;">
                    <strong>Signatures:<br>Service Provider:</strong><br><br>
                    _______________________<br><br>
                    <strong>Date: {{ date('d/m/Y') }}</strong>
                </td>
                <td width="30%" style="vertical-align: top; text-align: left;">
                    <strong>Signatures:<br>Customer:</strong><br><br>
                    _______________________<br><br>
                    <strong>Date: {{ date('d/m/Y') }}</strong>
                </td>
            </tr>
        </table>

        <p style="text-align: center; margin-top: 30px; font-style: italic;">
            This is a computer-generated print and does not require a physical signature.
        </p>
    </footer>
</body>

</html>
