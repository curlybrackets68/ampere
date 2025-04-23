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
            margin-bottom: 5px;
            font-weight: bold;
            margin-top: 5px;
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
                    <img src="{{ public_path('dist/assets/img/pdf-header.png') }}"
                        style="width: 100% !important; margin-top: -30px;">
                </td>
            </tr>
        </table>
    </header>
    <div class="title">AMC<br>VEHICLE SERVICE CONTRACT</div>

    <div class="section">Vehicle Details</div>
    <table>
        <tr>
            <td>Vehicle Category: [Old/New]</td>
            <td class="contract-dates">
                <strong>Contract Start Date:</strong> {{ $contract_start }}
            </td>
        </tr>
        <tr>
            <td>Vehicle Make & Model: {{ $vehicle_model }}</td>
            <td class="contract-dates">
                <span style="color: red;"><strong>Contract End Date:</strong> {{ $contract_end }}</strong></span>
            </td>
        </tr>
        <tr>
            <td>Chassis Number: {{ $chassis_number }}</td>
            <td></td>
        </tr>
    </table>

    <div class="section">Customer Details</div>
    <table>
        <tr>
            <td style="width: 60%">Name: <strong>{{ $customer_name }}</strong></td>
            <td style="float: right; text-align: right; width: 40%">Mobile Number:
                <strong>{{ $customer_mobile }}</strong>
            </td>
        </tr>
        <tr>
            <td>Address: <strong>{{ $customer_address }}</strong></td>
            <td></td>
        </tr>
    </table>

    <div class="section">Service Details</div>

    <table style="text-align: center;">
        <tr>
            <th colspan="4" style="text-align: right;">
                <div style="display: inline-block;">Package: <strong>{{ $package }}</strong></div>
            </th>
        </tr>
        <tr>
            <th class="serviceTh" style="width: 10%;">Sr.No</th>
            <th class="serviceTh" style="width: 20%;">Service Date</th>
            <th class="serviceTh" style="width: 60%;">Service Remark</th>
            <th class="serviceTh" style="width: 10%;">Status</th>
        </tr>
        @foreach ($services as $index => $service)
            <tr>
                <td class="serviceTd">{{ $index + 1 }}</td>
                <td class="serviceTd">{{ $service['date'] }}</td>
                <td class="serviceTd">{{ $service['remark'] ?? '' }}</td>
                <td class="serviceTd">{{ $service['status'] ?? '' }}</td>
            </tr>
        @endforeach
    </table>

    <div class="section">Terms & Conditions</div>
    <ol class="terms">
        <li>This contract includes <strong>{{ $services_count }} paid services</strong>...</li>
        <li>The contract will expire on the End Date...</li>
        <li>If all 4 services are availed before the contract end date...</li>
        <li>Any unutilized services will lapse...</li>
        <li>The cost of spare parts, additional labour...</li>
        <li>Services must be availed only at authorized centers...</li>
    </ol>

    <div class="section">Payment Details</div>
    <table>
        <tr>
            <td style="text-align: left;">Payment Type: <strong> Online </strong></td>
            <td style="text-align: right;">
                AMC Amount: <strong>2000 </strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">Transaction No: <strong>0250255336482121</strong></td>
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
