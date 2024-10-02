<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propovoice Email</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>


</head>

<body class="ndpv" style="font-family: 'Inter', sans-serif;margin: 0;padding: 40px 0 60px;background-color: aliceblue;-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;height: 100%;width: 100% !important;">
  <table class="ndpv-container" style="border-collapse: collapse;max-width: 559px;margin: 0 auto;margin-top: 50px;display: block !important;">
    <tbody>
      <tr>
        <td>
          <table class="ndpv-table-container" style="padding: 30px 30px 60px 40px;border-spacing: 0;border-collapse: collapse;border-top: 10px solid #4c6fff;border-radius: 17px;max-width: 559px;background-color: #fff;margin: 0 auto;margin-bottom: 20px;display: block !important;">
            <tbody>
              <tr>
                <td>
                  <table class="ndpv-head" style="min-width: 490px;">
                    <tr>
                      <td class="ndpv-logo" style="width: 200px;">
                        {org_img}
                      </td>
                      <td class="ndpv-address" style="width: 202px;padding: 13px 13px 13px 20px;background-color: #EDF2F7;border-radius: 9px;">
                        <h6 style="font-family: 'Inter', sans-serif;font-style: normal;font-weight: 600;font-size: 14px;line-height: 150%;color: #2D3748;margin-bottom: 2px;">{org_name}</h6>
                        <p style="font-family: 'Inter';font-style: normal;font-weight: 400;font-size: 12px;line-height: 140%;color: #4A5568;">
                          {org_address}
                        </p>
                      </td>
                    </tr>
                  </table>
                </td>

              </tr>
              <tr>
                <td class="ndpv-msg" style="display: inline-table;margin: 35px 0 45px 0;">
                  <p style="font-family: 'Inter';font-style: normal;font-weight: 400;font-size: 14px;line-height: 160%;color: #1A202C;display: inline-block;">{msg}</p>
                </td>
              </tr>
              <tr>
                <td class="ndpv-view" style="text-align: center;">
                  <a href="{url}" target="_blank" class="ndpv-btn" style="font-family: 'Inter';font-style: normal;font-weight: 600;font-size: 14px;line-height: 14px;padding: 16px 38px;text-decoration: none;background-color: #4C6FFF;border: 1px solid #4C6FFF;border-radius: 8px;margin-bottom: 160px;color: #fff !important;">{view_txt} {title}</a>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <footer>
            <table class="ndpv-footer-container" style="overflow: hidden;border-spacing: 0;text-align: center;width: 559px;margin: 0 auto;">
              <tbody>
                <tr>
                  <td class="ndpv-footer-text">
                    {footer_text}
                    <ul class="ndpv-social" style="display:table;margin:16px auto 0 auto;padding:0;text-align:center;">
                      {social}
                    </ul>
                  </td>
                </tr>
              </tbody>
            </table>
          </footer>
        </td>
      </tr>
    </tbody>
  </table>
</body>

</html>
