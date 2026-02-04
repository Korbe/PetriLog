<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetriLog</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #718096;
            line-height: 1.4;
        }

        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }

            .logo {
                max-width: 150px !important;
                height: auto !important;
                display: block;
                margin: 0 auto;
            }
        }

        a {
            color: #3869d4;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation"
        style="background-color:#ffffff; width:100%; margin:0; padding:0;">
        <tr>
            <td align="center">

                <!-- Header -->
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header" style="padding:25px 0; text-align:center;">
                            <a href="{{ url('/') }}" style="display:inline-block; text-decoration:none;">
                                <img src="https://petrilog.com/logo.png" alt="Logo" class="logo" width="250"
                                    style="display:block; max-width:100%; height:auto; border:none;">
                            </a>
                        </td>
                    </tr>

                    <!-- Body Slot -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0"
                            style="background-color:#ffffff; padding:0;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                                role="presentation"
                                style="background-color:#ffffff; border-radius:2px; margin:0 auto; padding:0; width:570px;">
                                <tr>
                                    <td class="content-cell" style="padding:32px; max-width:100vw;">
                                        {{ $slot }}

                                        <!-- Standard Footer innerhalb des Mails -->
                                        <p style="margin-top: 32px; font-size: 16px; line-height:1.5;">
                                            Danke & Petri Heil 🎣<br>
                                            {{ config('app.name') }}
                                        </p>

                                        <!-- Optional Subcopy Slot -->
                                        @isset($subcopy)
                                            <table class="subcopy" width="100%" cellpadding="0" cellspacing="0"
                                                role="presentation"
                                                style="border-top:1px solid #e8e5ef; margin-top:25px; padding-top:25px;">
                                                <tr>
                                                    <td style="font-size:12px; color:#718096;">
                                                        {{ $subcopy }}
                                                    </td>
                                                </tr>
                                            </table>
                                        @endisset
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Bottom Footer -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <tr>
                                    <td class="content-cell" align="center"
                                        style="padding:32px; font-size:12px; color:#b0adc5;">
                                        © {{ date('Y') }} PetriLog. Alle Rechte vorbehalten.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
