<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <style>
            body {
                font-family: sans-serif;
                font-size: 13px;
            }
            table {
                table-layout: fixed;
                border-collapse: collapse;
                width: 100%;
                word-wrap: break-word;
            }
            th, td {
                text-align: center;
                vertical-align: top;
            }
            td {
                font-size: 12px;
                padding-bottom: 10px;
            }

            .title {
                font-size: 12px;
                font-weight: bold;
                background-color: #CCCCCC;
                text-align: center;
                padding-top: 5px;
                padding-bottom: 5px;
            }

            .content {
                padding-top: 20px;
                padding-bottom: 20px;
                text-align: center;
            }

        </style>
    </head>
    <body>
        <div style="width: 70%; float: left; margin-top: 17px; height: 300px;">
            <div style="width:49%; float: left; font-size: 11px;">
                Appraisal Scope, Inc. <br/>
                1421 Clarkview Rd #205, Baltimore, MD 21209 <br/>
                Phone: 1-800-321-0123 <br/>
                E-mail: <b>info@appraisalscope.com</b>
            </div>
            <div style="width: 49%; float: right; margin-right: 10px;">
                <img src="{{ base_path('resources/views/invoices/logo.png') }}" style="width: 100%;" alt=""/>
            </div>
            <br style="clear: both"/>
            <br/>
            <br/>
            <br/>
            <div style="border: 1px solid #000000; margin-right: 10px; padding: 5px; padding-top: 10px;">
                <div style="font-size: 14px; font-weight: bold;">TO:</div>
                <p style="font-size: 11px;">
                    {{ $amc['name'] }} <br/>
                    {{ $amc['address'] }} <br/>
                    <br/>

                    Phone #: {{ $amc['phone'] }} <br/>
                    @if ($amc['fax'])
                    Fax #: {{ $amc['fax'] }} <br/>
                    @endif
                    Email: {{ $amc['email'] }}
                    <br/><br/>
                </p>
            </div>
        </div>
        <div style="width: 30%; float: right;">
            <div style="text-align: center; font-size: 26px; font-weight: bold; margin-bottom: 7px;">INVOICE</div>
            <div style="border: 1px solid #000000; padding: 3px; height: 257px;">
                <div class="title">
                    INVOICE NUMBER
                </div>
                <div class="content">{{ $invoice['id'] }}</div>

                <div class="title">
                    DATE
                </div>
                <div class="content">{{ $invoice['createdAt'] }}</div>
            </div>
        </div>
        <br style="clear: both"/>
        <div class="title" style="border: 1px solid #000000; text-align: left; margin-bottom: 20px; padding-left: 5px;">
            ORDERS
        </div>
        <table>
            <tr>
                <th>File#</th>
                <th style="width: 15%">Job Type</th>
                <th>Loan#</th>
                <th>Borrower Name</th>
                <th style="width: 15%">Address</th>
                <th>Order Date</th>
                <th>Completed Date</th>
                <th style="width: 10%">Amount</th>
            </tr>

            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['file'] }}</td>
                    <td>{{ $item['jobType'] }}</td>
                    <td>{{ $item['loan'] }}</td>
                    <td>{{ $item['borrower'] }}</td>
                    <td>{{ $item['address'] }}</td>
                    <td>{{ $item['orderedAt'] }}</td>
                    <td>{{ $item['completedAt'] }}</td>
                    <td>{{ $item['amount'] }}</td>
                </tr>
            @endforeach
        </table>
        <div style="text-align: right; padding-top: 7px; font-size: 13px; border-top: 3px solid #7c7c7c; font-weight: bold;">
            TOTAL DUE : {{ $amount }}
        </div>
    </body>
</html>
