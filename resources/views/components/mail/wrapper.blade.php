<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#ffffff; width:100%; margin:0; padding:0;">
    <tr>
        <td align="center">
            <!-- Header -->
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="header" style="padding:25px 0; text-align:center;">
                        <a href="{{ url('/') }}" style="font-size:19px; font-weight:bold; text-decoration:none;">
                            <img src="https://petrilog.com/logo.png" alt="Logo" style="max-width:100%; height:100px; border:none;">
                        </a>
                    </td>
                </tr>

                <!-- Body Slot -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; padding:0;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#ffffff; border-radius:2px; margin:0 auto; padding:0; width:570px;">
                            <tr>
                                <td class="content-cell" style="padding:32px; max-width:100vw;">
                                    {{ $slot }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td>
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td class="content-cell" align="center" style="padding:32px; font-size:12px; color:#b0adc5;">
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