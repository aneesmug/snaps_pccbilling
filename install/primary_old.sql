# Dump of table sys_appconfig
# ------------------------------------------------------------

CREATE TABLE `sys_appconfig` (
                                 `id` int(11) NOT NULL AUTO_INCREMENT,
                                 `setting` varchar(200) NOT NULL DEFAULT '',
                                 `value` text,
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `sys_appconfig` (`setting`, `value`)
VALUES
('CompanyName','SnapSProduction'),
('theme','default'),
('currency_code','ر.س'),
('language','en'),
('show-logo','1'),
('nstyle','light_mode'),
('dec_point','.'),
('thousands_sep',','),
('timezone','Asia/Riyadh'),
('country','Saudi Arabia'),
('country_code','SA'),
('df','Y-m-d'),
('caddress','Prince Sultan Street<br>Dist. Basateen <br> Jeddah, Saudi Arabia'),
('account_search','1'),
('redirect_url','dashboard'),
('rtl','0'),
('ckey','0982995697'),
('networth_goal','350000'),
('sysEmail','demo@example.com'),
('url_rewrite','0'),
('build','----clx_file_build_placeholder----'),
('version','9.5.1'),
('animate','0'),
('pdf_font','dejavusanscondensed'),
('accounting','1'),
('invoicing','1'),
('quotes','1'),
('client_dashboard','1'),
('contact_set_view_mode','search'),
('invoice_terms',''),
('console_notify_invoice_created','0'),
('i_driver','v2'),
('purchase_key',''),
('c_cache',''),
('mininav','0'),
('hide_footer','1'),
('design','default'),
('default_landing_page','login'),
('recaptcha','0'),
('recaptcha_sitekey',''),
('recaptcha_secretkey',''),
('home_currency','SAR'),
('currency_decimal_digits','true'),
('currency_symbol_position','p'),
('thousand_separator_placement','3'),
('dashboard','canvas'),
('header_scripts',''),
('footer_scripts',''),
('ib_key','vLBLfhA6DNi1R2MFHO8IvFWr4Cn9665eHUF+L/sqAKM='),
('ib_s','PNhjeZ0sOFF3JNfzT2mLxvNNKPeh6ltqpE+G5LVSDSvgp/z79Sco7W4tJEoXYIl8'),
('ib_u_t','1512841700'),
('ib_u_a','0'),
('momentLocale','en'),
('contentAnimation','animated fadeIn'),
('calendar','1'),
('leads','1'),
('tasks','1'),
('orders','1'),
('show_quantity_as',''),
('gmap_api_key',''),
('license_key',''),
('local_key',''),
('ib_installed_at','1485170108'),
('maxmind_installed','1'),
('maxmind_db_version',''),
('add_fund','1'),
('add_fund_minimum_deposit','100'),
('add_fund_maximum_deposit','2500'),
('add_fund_maximum_balance','25000'),
('add_fund_require_active_order','0'),
('industry','default'),
('sales_target','10000'),
('inventory','1'),
('secondary_currency',''),
('customer_custom_username','1'),
('documents','1'),
('projects','0'),
('purchase','1'),
('suppliers','1'),
('support','1'),
('hrm','1'),
('companies','1'),
('plugins','1'),
('country_flag_code','us'),
('graph_primary_color','002868'),
('graph_secondary_color','dc171d'),
('expense_type_1','Personal'),
('expense_type_2','Business'),
('edition','default'),
('assets','1'),
('kb','1'),
('business_id_1',''),
('business_id_2',''),
('logo_default','logo_2105025689.png'),
('logo_inverse','logo_inverse_7627893866.png'),
('add_fund_client','1'),
('order_method','default'),
('purchase_code',''),
('invoice_receipt_number','0'),
('allow_customer_registration','1'),
('fax_field','0'),
('show_business_number','1'),
('label_business_number','Business Number'),
('sms','1'),
('sms_request_method','POST'),
('sms_auth_header',''),
('sms_req_url',''),
('sms_notify_admin_new_deposit','0'),
('sms_notify_customer_signed_up','0'),
('sms_notify_customer_invoice_created','0'),
('sms_notify_customer_invoice_paid','0'),
('sms_notify_customer_payment_received','0'),
('sms_api_handler','Nexmo'),
('sms_auth_username',''),
('sms_auth_password',''),
('sms_sender_name','CLX'),
('sms_http_params',''),
('purchase_invoice_payment_status','0'),
('quote_confirmation_email','1'),
('client_drive','1'),
('s_version','7'),
('latest_file',''),
('file_public_key','d050d069-c43d-4c7c-a478-8c9917c3ac2f'),
('cache_id','1000'),
('invoice_show_watermark','1'),
('show_country_flag','0'),
('drive','0'),
('tax_system','saudi_zatca'),
('pos','1'),
('password_manager','default'),
('update_manager','1'),
('app_credentials','0'),
('business_location','Jeddah KSA.'),
('hr','1'),
('mailgun_api_key',''),
('sparkpost_api_key',''),
('mailgun_domain',''),
('show_sidebar_header','1'),
('top_bar_is_dark','1'),
('slack_webhook_url',''),
('config_recaptcha_in_client_login','0'),
('config_recaptcha_in_admin_login','0'),
('contact_list_show_company_column','0'),
('config_contact_list_show_group_column','1'),
('contact_list_show_group_column','0'),
('tickets_assigned_sms_notification','1'),
('admin_layout','layouts/admin.tpl'),
('client_layout','layouts/client.tpl'),
('route_controller_directory','default'),
('disabled_theme_options','0'),
('number_pad', '5'),
('customer_code_prefix', 'CUS-'),
('customer_code_template', ''),
('customer_code_current_number', '1'),
('supplier_code_prefix', 'SUP-'),
('supplier_code_template', ''),
('supplier_code_current_number', '1'),
('income_code_prefix', 'INC-'),
('income_code_template', ''),
('income_code_current_number', '1'),
('expense_code_prefix', 'EXP-'),
('expense_code_template', ''),
('expense_code_current_number', '1'),
('invoice_code_prefix', 'INV-'),
('invoice_code_template', ''),
('invoice_code_current_number', '1'),
('quotation_code_prefix', 'QUOTE-'),
('quotation_code_template', ''),
('quotation_code_current_number', '1'),
('purchase_code_prefix', 'PO-'),
('purchase_code_template', ''),
('purchase_code_current_number', '1'),
('contact_display_name_string', 'Display Name'),
('contact_extra_field', 'Display Name'),
('company_code_prefix', 'COMP-'),
('company_code_template', ''),
('company_code_current_number', '1'),
('ticket_code_prefix', ''),
('ticket_code_template', ''),
('ticket_code_current_number', ''),
('invoice_show_qr_code', '1'),
('zatca_enabled', '1'),
('zatca_phase2_enabled', '1'),
('zatca_environment', 'sandbox'),
('zatca_seller_name', 'SnapS Production House'),
('zatca_vat_number', '399999999900003'),
('zatca_seller_crn', '7003478593'),
('zatca_building_no', '7788'),
('zatca_street_name', 'Sandbox Street 12'),
('zatca_district', 'Makkah'),
('zatca_city', 'Jeddah'),
('zatca_postal_code', '12211'),
('zatca_country_code', 'SA'),
('zatca_otp', '556788'),
('zatca_compliance_csid', '1234567890123'),
('zatca_compliance_secret', ''),
('zatca_production_csid', 'TUlJRDNqQ0NBNFNnQXdJQkFnSVRFUUFBT0FQRjkwQWpzL3hjWHdBQkFBQTRBekFLQmdncWhrak9QUVFEQWpCaU1SVXdFd1lLQ1pJbWlaUHlMR1FCR1JZRmJHOWpZV3d4RXpBUkJnb0praWFKay9Jc1pBRVpGZ05uYjNZeEZ6QVZCZ29Ka2lhSmsvSXNaQUVaRmdkbGVIUm5ZWHAwTVJzd0dRWURWUVFERXhKUVVscEZTVTVXVDBsRFJWTkRRVFF0UTBFd0hoY05NalF3TVRFeE1Ea3hPVE13V2hjTk1qa3dNVEE1TURreE9UTXdXakIxTVFzd0NRWURWUVFHRXdKVFFURW1NQ1FHQTFVRUNoTWRUV0Y0YVcxMWJTQlRjR1ZsWkNCVVpXTm9JRk4xY0hCc2VTQk1WRVF4RmpBVUJnTlZCQXNURFZKcGVXRmthQ0JDY21GdVkyZ3hKakFrQmdOVkJBTVRIVlJUVkMwNE9EWTBNekV4TkRVdE16azVPVGs1T1RrNU9UQXdNREF6TUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVvV0NLYTBTYTlGSUVyVE92MHVBa0MxVklLWHhVOW5QcHgydmxmNHloTWVqeThjMDJYSmJsRHE3dFB5ZG84bXEwYWhPTW1Obzhnd25pN1h0MUtUOVVlS09DQWdjd2dnSURNSUd0QmdOVkhSRUVnYVV3Z2FLa2daOHdnWnd4T3pBNUJnTlZCQVFNTWpFdFZGTlVmREl0VkZOVWZETXRaV1F5TW1ZeFpEZ3RaVFpoTWkweE1URTRMVGxpTlRndFpEbGhPR1l4TVdVME5EVm1NUjh3SFFZS0NaSW1pWlB5TEdRQkFRd1BNems1T1RrNU9UazVPVEF3TURBek1RMHdDd1lEVlFRTURBUXhNVEF3TVJFd0R3WURWUVFhREFoU1VsSkVNamt5T1RFYU1CZ0dBMVVFRHd3UlUzVndjR3g1SUdGamRHbDJhWFJwWlhNd0hRWURWUjBPQkJZRUZFWCtZdm1tdG5Zb0RmOUJHYktvN29jVEtZSzFNQjhHQTFVZEl3UVlNQmFBRkp2S3FxTHRtcXdza0lGelZ2cFAyUHhUKzlObk1Ic0dDQ3NHQVFVRkJ3RUJCRzh3YlRCckJnZ3JCZ0VGQlFjd0FvWmZhSFIwY0RvdkwyRnBZVFF1ZW1GMFkyRXVaMjkyTG5OaEwwTmxjblJGYm5KdmJHd3ZVRkphUlVsdWRtOXBZMlZUUTBFMExtVjRkR2RoZW5RdVoyOTJMbXh2WTJGc1gxQlNXa1ZKVGxaUFNVTkZVME5CTkMxRFFTZ3hLUzVqY25Rd0RnWURWUjBQQVFIL0JBUURBZ2VBTUR3R0NTc0dBUVFCZ2pjVkJ3UXZNQzBHSlNzR0FRUUJnamNWQ0lHR3FCMkUwUHNTaHUyZEpJZk8reG5Ud0ZWbWgvcWxaWVhaaEQ0Q0FXUUNBUkl3SFFZRFZSMGxCQll3RkFZSUt3WUJCUVVIQXdNR0NDc0dBUVVGQndNQ01DY0dDU3NHQVFRQmdqY1ZDZ1FhTUJnd0NnWUlLd1lCQlFVSEF3TXdDZ1lJS3dZQkJRVUhBd0l3Q2dZSUtvWkl6ajBFQXdJRFNBQXdSUUloQUxFL2ljaG1uV1hDVUtVYmNhM3ljaThvcXdhTHZGZEhWalFydmVJOXVxQWJBaUE5aEM0TThqZ01CQURQU3ptZDJ1aVBKQTZnS1IzTEUwM1U3NWVxYkMvclhBPT0='),
('zatca_production_secret', 'CkYsEXfV8c1gFHAtFWoZv73pGMvh/Qyo4LzKM2h/8Hg='),
('zatca_api_base_url', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal'),
('zatca_invoice_type', 'simplified'),
('zatca_csr_content', 'MIIB7jCCAZMCAQAwXzELMAkGA1UEBhMCU0ExEzARBgNVBAsMCjMxMjM0NTY3ODkx\r\nEzARBgNVBAoMCjMxMjM0NTY3ODkxJjAkBgNVBAMMHVRTVC04ODY0MzExNDUtMzEy\r\nMzQ1Njc4OTAwMDAzMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEC0rSAo1ZILALYxf9\r\nOulI3o+KRXEr6LP+vDp3vCw3W4gBogrc9t1870AJ14osoxtDkNLAZUbr4HX4ikQg\r\ngPGy3aCB1DCB0QYJKoZIhvcNAQkOMYHDMIHAMCEGCSsGAQQBgjcUAgQUDBJaQVRD\r\nQS1Db2RlLVNpZ25pbmcwgZoGA1UdEQSBkjCBj6SBjDCBiTE7MDkGA1UEBAwyMS1U\r\nU1R8Mi1UU1R8My0zZjJmNmQ1ZS04ZjcwLTRhODktYmY2ZS1kNGE0ZjBkZDFjOWEx\r\nHzAdBgoJkiaJk/IsZAEBDA8zMTIzNDU2Nzg5MDAwMDMxDTALBgNVBAwMBDExMDAx\r\nDDAKBgNVBBoMA1RTVDEMMAoGA1UEDwwDVFNUMAoGCCqGSM49BAMCA0kAMEYCIQDB\r\n/zpMBNL2/J0PDNDwGkr4J77VbhlJORyrLyyeK4SthgIhAJqlcQxgCHC/8hAwyp4x\r\nv5kTp2cclACfkhN/i7LqdYhj'),
('zatca_binary_security_token', 'TUlJQ1BEQ0NBZU9nQXdJQkFnSUdBWjNCc1dNSE1Bb0dDQ3FHU000OUJBTUNNQlV4RXpBUkJnTlZCQU1NQ21WSmJuWnZhV05wYm1jd0hoY05Nall3TkRJME1qSTFNVFUyV2hjTk16RXdOREkwTWpFd01EQXdXakIxTVFzd0NRWURWUVFHRXdKVFFURVdNQlFHQTFVRUN3d05VbWw1WVdSb0lFSnlZVzVqYURFbU1DUUdBMVVFQ2d3ZFRXRjRhVzExYlNCVGNHVmxaQ0JVWldOb0lGTjFjSEJzZVNCTVZFUXhKakFrQmdOVkJBTU1IVlJUVkMwNE9EWTBNekV4TkRVdE16azVPVGs1T1RrNU9UQXdNREF6TUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVvV0NLYTBTYTlGSUVyVE92MHVBa0MxVklLWHhVOW5QcHgydmxmNHloTWVqeThjMDJYSmJsRHE3dFB5ZG84bXEwYWhPTW1Obzhnd25pN1h0MUtUOVVlS09Cd1RDQnZqQU1CZ05WSFJNQkFmOEVBakFBTUlHdEJnTlZIUkVFZ2FVd2dhS2tnWjh3Z1p3eE96QTVCZ05WQkFRTU1qRXRWRk5VZkRJdFZGTlVmRE10WldReU1tWXhaRGd0WlRaaE1pMHhNVEU0TFRsaU5UZ3RaRGxoT0dZeE1XVTBORFZtTVI4d0hRWUtDWkltaVpQeUxHUUJBUXdQTXprNU9UazVPVGs1T1RBd01EQXpNUTB3Q3dZRFZRUU1EQVF4TVRBd01SRXdEd1lEVlFRYURBaFNVbEpFTWpreU9URWFNQmdHQTFVRUR3d1JVM1Z3Y0d4NUlHRmpkR2wyYVhScFpYTXdDZ1lJS29aSXpqMEVBd0lEUndBd1JBSWdWRzdNZi9hczAwNlNudkNIN3c3NmNGbnVjWHY3TEw2dzRPcDAzL0J6ZkNJQ0lISklGa0xmSXdIM3NKK3NiTjRmNXVmVlV5MXNkWUtrQ05pUndMS1BiTEhF'),
('zatca_secret', '8EkRUh7bmin9hAM5S40kmu5g3eiU/OPwEz/9ua3IUuA='),
('zatca_compliance_request_id', '1234567890123'),
('zatca_private_key_passphrase', ''),
('zatca_private_key', 'D:\\xampp\\htdocs\\ibilling_sute\\zatca-envoice-sdk-203\\Data\\Certificates\\my-zatca-private-key.pem'),
('zatca_certificate', '-----BEGIN CERTIFICATE-----\r\nTUlJQ1BUQ0NBZU9nQXdJQkFnSUdBWjIyaVVNY01Bb0dDQ3FHU000OUJBTUNNQlV4RXpBUkJnTlZCQU1NQ21WSmJuWnZhV05wYm1jd0hoY05Nall3TkRJeU1UZzFNakUzV2hjTk16RXdOREl4TWpFd01EQXdXakIxTVFzd0NRWURWUVFHRXdKVFFURVdNQlFHQTFVRUN3d05VbWw1WVdSb0lFSnlZVzVqYURFbU1DUUdBMVVFQ2d3ZFRXRjRhVzExYlNCVGNHVmxaQ0JVWldOb0lGTjFjSEJzZVNCTVZFUXhKakFrQmdOVkJBTU1IVlJUVkMwNE9EWTBNekV4TkRVdE16azVPVGs1T1RrNU9UQXdNREF6TUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVvV0NLYTBTYTlGSUVyVE92MHVBa0MxVklLWHhVOW5QcHgydmxmNHloTWVqeThjMDJYSmJsRHE3dFB5ZG84bXEwYWhPTW1Obzhnd25pN1h0MUtUOVVlS09Cd1RDQnZqQU1CZ05WSFJNQkFmOEVBakFBTUlHdEJnTlZIUkVFZ2FVd2dhS2tnWjh3Z1p3eE96QTVCZ05WQkFRTU1qRXRWRk5VZkRJdFZGTlVmRE10WldReU1tWXhaRGd0WlRaaE1pMHhNVEU0TFRsaU5UZ3RaRGxoT0dZeE1XVTBORFZtTVI4d0hRWUtDWkltaVpQeUxHUUJBUXdQTXprNU9UazVPVGs1T1RBd01EQXpNUTB3Q3dZRFZRUU1EQVF4TVRBd01SRXdEd1lEVlFRYURBaFNVbEpFTWpreU9URWFNQmdHQTFVRUR3d1JVM1Z3Y0d4NUlHRmpkR2wyYVhScFpYTXdDZ1lJS29aSXpqMEVBd0lEU0FBd1JRSWhBTmhSYkhvWDM3dGJqNk1WS1VTSE1GMytPalRuTmlSNmx0VE93NXNsT1E0NUFpQlpDdDFXU2dtMzVMSEp4cmx5WjU3M003QzdZbUR4U1dDbEM2Ynp5ci91eXc9PQ==\r\n-----END CERTIFICATE-----'),
('zatca_production_binary_security_token', 'TUlJRDNqQ0NBNFNnQXdJQkFnSVRFUUFBT0FQRjkwQWpzL3hjWHdBQkFBQTRBekFLQmdncWhrak9QUVFEQWpCaU1SVXdFd1lLQ1pJbWlaUHlMR1FCR1JZRmJHOWpZV3d4RXpBUkJnb0praWFKay9Jc1pBRVpGZ05uYjNZeEZ6QVZCZ29Ka2lhSmsvSXNaQUVaRmdkbGVIUm5ZWHAwTVJzd0dRWURWUVFERXhKUVVscEZTVTVXVDBsRFJWTkRRVFF0UTBFd0hoY05NalF3TVRFeE1Ea3hPVE13V2hjTk1qa3dNVEE1TURreE9UTXdXakIxTVFzd0NRWURWUVFHRXdKVFFURW1NQ1FHQTFVRUNoTWRUV0Y0YVcxMWJTQlRjR1ZsWkNCVVpXTm9JRk4xY0hCc2VTQk1WRVF4RmpBVUJnTlZCQXNURFZKcGVXRmthQ0JDY21GdVkyZ3hKakFrQmdOVkJBTVRIVlJUVkMwNE9EWTBNekV4TkRVdE16azVPVGs1T1RrNU9UQXdNREF6TUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVvV0NLYTBTYTlGSUVyVE92MHVBa0MxVklLWHhVOW5QcHgydmxmNHloTWVqeThjMDJYSmJsRHE3dFB5ZG84bXEwYWhPTW1Obzhnd25pN1h0MUtUOVVlS09DQWdjd2dnSURNSUd0QmdOVkhSRUVnYVV3Z2FLa2daOHdnWnd4T3pBNUJnTlZCQVFNTWpFdFZGTlVmREl0VkZOVWZETXRaV1F5TW1ZeFpEZ3RaVFpoTWkweE1URTRMVGxpTlRndFpEbGhPR1l4TVdVME5EVm1NUjh3SFFZS0NaSW1pWlB5TEdRQkFRd1BNems1T1RrNU9UazVPVEF3TURBek1RMHdDd1lEVlFRTURBUXhNVEF3TVJFd0R3WURWUVFhREFoU1VsSkVNamt5T1RFYU1CZ0dBMVVFRHd3UlUzVndjR3g1SUdGamRHbDJhWFJwWlhNd0hRWURWUjBPQkJZRUZFWCtZdm1tdG5Zb0RmOUJHYktvN29jVEtZSzFNQjhHQTFVZEl3UVlNQmFBRkp2S3FxTHRtcXdza0lGelZ2cFAyUHhUKzlObk1Ic0dDQ3NHQVFVRkJ3RUJCRzh3YlRCckJnZ3JCZ0VGQlFjd0FvWmZhSFIwY0RvdkwyRnBZVFF1ZW1GMFkyRXVaMjkyTG5OaEwwTmxjblJGYm5KdmJHd3ZVRkphUlVsdWRtOXBZMlZUUTBFMExtVjRkR2RoZW5RdVoyOTJMbXh2WTJGc1gxQlNXa1ZKVGxaUFNVTkZVME5CTkMxRFFTZ3hLUzVqY25Rd0RnWURWUjBQQVFIL0JBUURBZ2VBTUR3R0NTc0dBUVFCZ2pjVkJ3UXZNQzBHSlNzR0FRUUJnamNWQ0lHR3FCMkUwUHNTaHUyZEpJZk8reG5Ud0ZWbWgvcWxaWVhaaEQ0Q0FXUUNBUkl3SFFZRFZSMGxCQll3RkFZSUt3WUJCUVVIQXdNR0NDc0dBUVVGQndNQ01DY0dDU3NHQVFRQmdqY1ZDZ1FhTUJnd0NnWUlLd1lCQlFVSEF3TXdDZ1lJS3dZQkJRVUhBd0l3Q2dZSUtvWkl6ajBFQXdJRFNBQXdSUUloQUxFL2ljaG1uV1hDVUtVYmNhM3ljaThvcXdhTHZGZEhWalFydmVJOXVxQWJBaUE5aEM0TThqZ01CQURQU3ptZDJ1aVBKQTZnS1IzTEUwM1U3NWVxYkMvclhBPT0='),
('zatca_compliance_last_response', 'Invalid Request'),
('zatca_compliance_invoices_last_response', '{\"validationResults\":{\"infoMessages\":[{\"type\":\"INFO\",\"code\":\"XSD_ZATCA_VALID\",\"category\":\"XSD validation\",\"message\":\"Complied with UBL 2.1 standards in line with ZATCA specifications\",\"status\":\"PASS\"}],\"warningMessages\":[{\"type\":\"WARNING\",\"code\":\"BR-KSA-27\",\"category\":\"KSA\",\"message\":\"[BR-KSA-27]-The document must contain a-- QR code (KSA-14), and this code must be base64Binary.\",\"status\":\"WARNING\"},{\"type\":\"WARNING\",\"code\":\"BR-KSA-08\",\"category\":\"KSA\",\"message\":\"The seller identification (BT-29) must exist only once with one of the scheme ID (BT-29-1) (CRN, MOM, MLS, SAG, OTH, 700) and must contain only alphanumeric characters. Commercial Registration number with \'CRN\' as schemeID. MOMRAH license with \'MOM\' as schemeID. MHRSD license with \'MLS\' as schemeID. 700 Number with \'700\' as schemeID. MISA license with \'SAG\' as schemeID. Other ID with \'OTH\' as schemeID. In case of multiple commercial registrations, the seller should fill the commercial registration of the branch in respect of which the Tax Invoice is being issued. In case multiple IDs exist then one of the above must be entered following the sequence specified above\",\"status\":\"WARNING\"},{\"type\":\"WARNING\",\"code\":\"BR-KSA-37\",\"category\":\"KSA\",\"message\":\"The seller address building number must contain 4 digits.\",\"status\":\"WARNING\"},{\"type\":\"WARNING\",\"code\":\"BR-KSA-09\",\"category\":\"KSA\",\"message\":\"Seller address must contain street name (BT-35), building number (KSA-17), postal code (BT-38), city (BT-37), district (KSA-3), country code (BT-40). For more information please access this link: https://splonline.com.sa/en/national-address-1/\",\"status\":\"WARNING\"},{\"type\":\"WARNING\",\"code\":\"BR-KSA-60\",\"category\":\"KSA\",\"message\":\"[BR-KSA-60]-Cryptographic stamp (KSA-15) must exist in simplified tax invoices and associated credit notes and debit notes (KSA-2, position 1 and 2 = 02).\",\"status\":\"WARNING\"},{\"type\":\"WARNING\",\"code\":\"BR-KSA-EN16931-09\",\"category\":\"KSA\",\"message\":\"[BR-KSA-EN16931-09]-Only one tax total (BG-22) without tax subtotals (BG-23) must be provided when tax currency code is provided.\",\"status\":\"WARNING\"}],\"errorMessages\":[{\"type\":\"ERROR\",\"code\":\"invalid-invoice-hash\",\"category\":\"INVOICE_HASHING_ERRORS\",\"message\":\"The invoice hash API body does not match the (calculated) Hash of the XML\",\"status\":\"ERROR\"},{\"type\":\"ERROR\",\"code\":\"QRCODE_INVALID\",\"category\":\"QRCODE_VALIDATION\",\"message\":\"Failed to validate QR code\",\"status\":\"ERROR\"}],\"status\":\"ERROR\"},\"reportingStatus\":\"NOT_REPORTED\",\"clearanceStatus\":null,\"qrSellertStatus\":null,\"qrBuyertStatus\":null}'),
('zatca_production_last_response', '{\"requestID\":30368,\"tokenType\":\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3\",\"dispositionMessage\":\"ISSUED\",\"binarySecurityToken\":\"TUlJRDNqQ0NBNFNnQXdJQkFnSVRFUUFBT0FQRjkwQWpzL3hjWHdBQkFBQTRBekFLQmdncWhrak9QUVFEQWpCaU1SVXdFd1lLQ1pJbWlaUHlMR1FCR1JZRmJHOWpZV3d4RXpBUkJnb0praWFKay9Jc1pBRVpGZ05uYjNZeEZ6QVZCZ29Ka2lhSmsvSXNaQUVaRmdkbGVIUm5ZWHAwTVJzd0dRWURWUVFERXhKUVVscEZTVTVXVDBsRFJWTkRRVFF0UTBFd0hoY05NalF3TVRFeE1Ea3hPVE13V2hjTk1qa3dNVEE1TURreE9UTXdXakIxTVFzd0NRWURWUVFHRXdKVFFURW1NQ1FHQTFVRUNoTWRUV0Y0YVcxMWJTQlRjR1ZsWkNCVVpXTm9JRk4xY0hCc2VTQk1WRVF4RmpBVUJnTlZCQXNURFZKcGVXRmthQ0JDY21GdVkyZ3hKakFrQmdOVkJBTVRIVlJUVkMwNE9EWTBNekV4TkRVdE16azVPVGs1T1RrNU9UQXdNREF6TUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVvV0NLYTBTYTlGSUVyVE92MHVBa0MxVklLWHhVOW5QcHgydmxmNHloTWVqeThjMDJYSmJsRHE3dFB5ZG84bXEwYWhPTW1Obzhnd25pN1h0MUtUOVVlS09DQWdjd2dnSURNSUd0QmdOVkhSRUVnYVV3Z2FLa2daOHdnWnd4T3pBNUJnTlZCQVFNTWpFdFZGTlVmREl0VkZOVWZETXRaV1F5TW1ZeFpEZ3RaVFpoTWkweE1URTRMVGxpTlRndFpEbGhPR1l4TVdVME5EVm1NUjh3SFFZS0NaSW1pWlB5TEdRQkFRd1BNems1T1RrNU9UazVPVEF3TURBek1RMHdDd1lEVlFRTURBUXhNVEF3TVJFd0R3WURWUVFhREFoU1VsSkVNamt5T1RFYU1CZ0dBMVVFRHd3UlUzVndjR3g1SUdGamRHbDJhWFJwWlhNd0hRWURWUjBPQkJZRUZFWCtZdm1tdG5Zb0RmOUJHYktvN29jVEtZSzFNQjhHQTFVZEl3UVlNQmFBRkp2S3FxTHRtcXdza0lGelZ2cFAyUHhUKzlObk1Ic0dDQ3NHQVFVRkJ3RUJCRzh3YlRCckJnZ3JCZ0VGQlFjd0FvWmZhSFIwY0RvdkwyRnBZVFF1ZW1GMFkyRXVaMjkyTG5OaEwwTmxjblJGYm5KdmJHd3ZVRkphUlVsdWRtOXBZMlZUUTBFMExtVjRkR2RoZW5RdVoyOTJMbXh2WTJGc1gxQlNXa1ZKVGxaUFNVTkZVME5CTkMxRFFTZ3hLUzVqY25Rd0RnWURWUjBQQVFIL0JBUURBZ2VBTUR3R0NTc0dBUVFCZ2pjVkJ3UXZNQzBHSlNzR0FRUUJnamNWQ0lHR3FCMkUwUHNTaHUyZEpJZk8reG5Ud0ZWbWgvcWxaWVhaaEQ0Q0FXUUNBUkl3SFFZRFZSMGxCQll3RkFZSUt3WUJCUVVIQXdNR0NDc0dBUVVGQndNQ01DY0dDU3NHQVFRQmdqY1ZDZ1FhTUJnd0NnWUlLd1lCQlFVSEF3TXdDZ1lJS3dZQkJRVUhBd0l3Q2dZSUtvWkl6ajBFQXdJRFNBQXdSUUloQUxFL2ljaG1uV1hDVUtVYmNhM3ljaThvcXdhTHZGZEhWalFydmVJOXVxQWJBaUE5aEM0TThqZ01CQURQU3ptZDJ1aVBKQTZnS1IzTEUwM1U3NWVxYkMvclhBPT0=\",\"secret\":\"CkYsEXfV8c1gFHAtFWoZv73pGMvh/Qyo4LzKM2h/8Hg=\"}'),
('zatca_production_certificate', '-----BEGIN CERTIFICATE-----\nMIID3jCCA4SgAwIBAgITEQAAOAPF90Ajs/xcXwABAAA4AzAKBggqhkjOPQQDAjBi\nMRUwEwYKCZImiZPyLGQBGRYFbG9jYWwxEzARBgoJkiaJk/IsZAEZFgNnb3YxFzAV\nBgoJkiaJk/IsZAEZFgdleHRnYXp0MRswGQYDVQQDExJQUlpFSU5WT0lDRVNDQTQt\nQ0EwHhcNMjQwMTExMDkxOTMwWhcNMjkwMTA5MDkxOTMwWjB1MQswCQYDVQQGEwJT\nQTEmMCQGA1UEChMdTWF4aW11bSBTcGVlZCBUZWNoIFN1cHBseSBMVEQxFjAUBgNV\nBAsTDVJpeWFkaCBCcmFuY2gxJjAkBgNVBAMTHVRTVC04ODY0MzExNDUtMzk5OTk5\nOTk5OTAwMDAzMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEoWCKa0Sa9FIErTOv0uAk\nC1VIKXxU9nPpx2vlf4yhMejy8c02XJblDq7tPydo8mq0ahOMmNo8gwni7Xt1KT9U\neKOCAgcwggIDMIGtBgNVHREEgaUwgaKkgZ8wgZwxOzA5BgNVBAQMMjEtVFNUfDIt\nVFNUfDMtZWQyMmYxZDgtZTZhMi0xMTE4LTliNTgtZDlhOGYxMWU0NDVmMR8wHQYK\nCZImiZPyLGQBAQwPMzk5OTk5OTk5OTAwMDAzMQ0wCwYDVQQMDAQxMTAwMREwDwYD\nVQQaDAhSUlJEMjkyOTEaMBgGA1UEDwwRU3VwcGx5IGFjdGl2aXRpZXMwHQYDVR0O\nBBYEFEX+YvmmtnYoDf9BGbKo7ocTKYK1MB8GA1UdIwQYMBaAFJvKqqLtmqwskIFz\nVvpP2PxT+9NnMHsGCCsGAQUFBwEBBG8wbTBrBggrBgEFBQcwAoZfaHR0cDovL2Fp\nYTQuemF0Y2EuZ292LnNhL0NlcnRFbnJvbGwvUFJaRUludm9pY2VTQ0E0LmV4dGdh\nenQuZ292LmxvY2FsX1BSWkVJTlZPSUNFU0NBNC1DQSgxKS5jcnQwDgYDVR0PAQH/\nBAQDAgeAMDwGCSsGAQQBgjcVBwQvMC0GJSsGAQQBgjcVCIGGqB2E0PsShu2dJIfO\n+xnTwFVmh/qlZYXZhD4CAWQCARIwHQYDVR0lBBYwFAYIKwYBBQUHAwMGCCsGAQUF\nBwMCMCcGCSsGAQQBgjcVCgQaMBgwCgYIKwYBBQUHAwMwCgYIKwYBBQUHAwIwCgYI\nKoZIzj0EAwIDSAAwRQIhALE/ichmnWXCUKUbca3yci8oqwaLvFdHVjQrveI9uqAb\nAiA9hC4M8jgMBADPSzmd2uiPJA6gKR3LE03U75eqbC/rXA==\n-----END CERTIFICATE-----\n'),
('zatca_production_request_id', '30368'),
('address_format', 'default'),
('vat_number', '15'),
('invoice_default_date', 'due_on_receipt'),
('label_tax_number', 'VAT'),
('invoice_logo_height', ''),
('invoice_footer_html', '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-top: 2px solid #333; margin-top: 20px; padding-top: 15px; font-family: Arial, sans-serif; font-size: 12px; color: #555;\">\r\n<tbody>\r\n<tr>\r\n	<td width=\"33%\" style=\"vertical-align: top; padding: 10px 5px;\">\r\n		<strong style=\"color: #333;\">Your Company Name</strong><br>\r\n		123 Main Street, Building 4<br>\r\n		Riyadh, Saudi Arabia 12345<br>\r\n		Tel: +966 11 000 0000\r\n	</td>\r\n	<td width=\"34%\" style=\"vertical-align: top; padding: 10px 5px; text-align: center;\">\r\n		<strong style=\"color: #333;\">Bank Details</strong><br>\r\n		Bank: Al Rajhi Bank<br>\r\n		IBAN: SA00 0000 0000 0000 0000 0000<br>\r\n		Account Name: Your Company LLC\r\n	</td>\r\n	<td width=\"33%\" style=\"vertical-align: top; padding: 10px 5px; text-align: right;\">\r\n		<strong style=\"color: #333;\">Contact Us</strong><br>\r\n		Email: info@yourcompany.com<br>\r\n		Website: <a href=\"http://www.yourcompany.com\">www.yourcompany.com</a><br>\r\n		VAT No: 300000000000003\r\n	</td>\r\n</tr>\r\n<tr>\r\n	<td colspan=\"3\" style=\"text-align: center; padding-top: 10px; border-top: 1px solid #ddd; color: #999; font-size: 11px;\">\r\n		Thank you for your business! Payment is due within 30 days of invoice date.\r\n	</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
('font_size', 'root-text'),
('logo_text', 'SnapS Productoin'),
('header_show_logo_as', 'logo_square')
;

# Dump of table account_balances
# ------------------------------------------------------------

CREATE TABLE `account_balances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `balance` decimal(16,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table app_notes
# ------------------------------------------------------------

CREATE TABLE `app_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `contents` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table app_password_manager
# ------------------------------------------------------------

CREATE TABLE `app_password_manager` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `username` varchar(200) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



INSERT INTO `app_password_manager` (`id`, `client_id`, `type`, `name`, `url`, `username`, `password`, `notes`, `created_at`, `updated_at`)
VALUES
	(4,294,NULL,'google 2','http://www.google.com','test','test','','2017-12-09 17:28:36','2017-12-09 18:03:02');




# Dump of table app_sms
# ------------------------------------------------------------

CREATE TABLE `app_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `req_time` datetime DEFAULT NULL,
  `sent_time` datetime DEFAULT NULL,
  `sms_from` text,
  `sms_to` text,
  `sms` text,
  `driver` text,
  `resp` text,
  `status` varchar(200) DEFAULT NULL,
  `stype` varchar(200) NOT NULL DEFAULT 'Sent',
  `cid` int(11) DEFAULT NULL,
  `aid` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `iid` int(11) DEFAULT NULL,
  `trid` int(11) DEFAULT NULL,
  `lid` int(11) DEFAULT NULL,
  `oid` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table app_sms_drivers
# ------------------------------------------------------------

CREATE TABLE `app_sms_drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dname` varchar(200) DEFAULT NULL,
  `handler` varchar(200) DEFAULT NULL,
  `weburl` varchar(200) DEFAULT NULL,
  `description` text,
  `url` varchar(200) DEFAULT NULL,
  `incoming_url` varchar(200) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `username` varchar(200) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `api_key` varchar(200) DEFAULT NULL,
  `api_secret` varchar(200) DEFAULT NULL,
  `route` varchar(200) DEFAULT NULL,
  `sender_id` varchar(100) DEFAULT NULL,
  `balance` decimal(14,2) DEFAULT NULL,
  `placeholder` text,
  `status` varchar(100) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



INSERT INTO `app_sms_drivers` (`id`, `dname`, `handler`, `weburl`, `description`, `url`, `incoming_url`, `method`, `username`, `password`, `api_key`, `api_secret`, `route`, `sender_id`, `balance`, `placeholder`, `status`, `is_active`, `created_at`, `updated_at`)
VALUES
	(135,'custom','custom','http://www.example.com','Your Custom Gateway','http://api.example.com','http://www.example.com/incoming/','GET','your_username','your_password','your_api_key','your_api_secret','1','CloudOnex',1.00,'{{url}}/send.php?username={{username}}&password={{password}}&from={{from}}&to={{to}}&message={{message}}','Sandbox',0,NULL,NULL),
	(136,'test',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL),
	(137,'custom','custom','http://www.example.com','Your Custom Gateway','http://api.example.com','http://www.example.com/incoming/','GET','your_username','your_password','your_api_key','your_api_secret','1','CloudOnex',1.00,'{{url}}/send.php?username={{username}}&password={{password}}&from={{from}}&to={{to}}&message={{message}}','Sandbox',0,NULL,NULL),
	(138,'test',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL);




# Dump of table app_sms_templates
# ------------------------------------------------------------

CREATE TABLE `app_sms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tpl` varchar(200) DEFAULT NULL,
  `sms` text,
  `status` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_at_2` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `app_sms_templates` (`id`, `tpl`, `sms`, `status`, `created_at`, `updated_at`, `updated_at_2`) VALUES
(1, 'Invoice Created', 'Your Invoice- {{invoice_id}} is created. To view your invoice- {{invoice_url}}', NULL, NULL, '2017-11-23 03:59:37', NULL),
(2, 'Invoice Payment Reminder', 'We have not received payment for invoice {{invoice_id}}, dated {{invoice_date}}. To view your invoice- {{invoice_url}}', NULL, NULL, '2017-11-23 04:01:14', NULL),
(3, 'Invoice Overdue Notice', 'Your Invoice- {{invoice_id}} is now overdue. To view your invoice- {{invoice_url}}', NULL, NULL, '2017-11-23 03:59:20', NULL),
(4, 'Invoice Payment Confirmation', 'We have received your Payment for Invoice ID- {{invoice_id}}', NULL, NULL, '2017-11-23 04:02:15', NULL),
(5, 'Invoice Refund Confirmation', 'Your Payment for {{invoice_id}} has been refunded.', NULL, NULL, '2017-11-23 04:03:19', NULL),
(6, 'Quote Created', 'A Quote has been created- {{quote_id}}. You can view this Quote- {{quote_url}}', NULL, NULL, '2017-11-23 04:03:19', NULL),
(7, 'Quote Accepted', 'Thanks for Accepting Quote - {{quote_id}}. You can view this Quote- {{quote_url}}', NULL, NULL, '2017-11-23 04:03:19', NULL),
(8, 'Quote Cancelled', 'Quote has been cancelled - {{quote_id}}. You can view this Quote- {{quote_url}}', NULL, NULL, '2017-11-23 04:03:19', NULL),
(9, 'Quote Accepted: Admin Notification', 'Quote - {{quote_id}} has been accepted. You can view this Quote- {{quote_url}}', NULL, NULL, '2017-11-23 04:03:19', NULL),
(10, 'Quote Cancelled: Admin Notification', 'Quote - {{quote_id}} has been Cancelled. You can view this Quote- {{quote_url}}', NULL, NULL, NULL, NULL),
(12, 'Ticket Assigned: Admin Notification', 'Ticket - {{ticket_id}} has been assigned to you.', NULL, '2018-10-24 15:33:32', '2018-10-24 15:33:32', NULL);


# Dump of table asset_categories
# ------------------------------------------------------------

CREATE TABLE `asset_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plural` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `asset_categories` (`id`, `parent_id`, `name`, `api_name`, `plural`, `slug`, `prefix`, `sl`, `is_active`, `is_default`, `sort_order`, `created_at`, `updated_at`)
VALUES
	(6,0,'Electronics','','','','','',1,0,1,'2018-11-06 05:11:40','2018-11-06 05:11:40'),
	(7,0,'Software','','','','','',1,0,1,'2018-11-06 05:11:46','2018-11-06 05:11:46');




# Dump of table assets
# ------------------------------------------------------------

CREATE TABLE `assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_purchased` date DEFAULT NULL,
  `supported_until` date DEFAULT NULL,
  `price` decimal(16,4) DEFAULT NULL,
  `depreciation` decimal(16,4) DEFAULT NULL,
  `serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `category_id` int(10) unsigned DEFAULT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `contact_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `assets` (`id`, `asset`, `name`, `brand`, `date_purchased`, `supported_until`, `price`, `depreciation`, `serial`, `image`, `status`, `notes`, `category_id`, `employee_id`, `contact_id`, `location_id`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	(8,'','Macbook Pro','','2018-11-06','2019-11-06',1250.0000,NULL,'SL37289274899',NULL,NULL,'',6,NULL,NULL,NULL,NULL,'2018-11-06 05:12:09','2018-11-06 05:12:23'),
	(9,'','CloudOnex Business Suite','','2018-11-06','2019-11-06',299.0000,NULL,'ABC-17284-278487-2222',NULL,NULL,'',7,NULL,NULL,NULL,NULL,'2018-11-06 05:13:02','2018-11-06 05:13:02');


# Dump of table attendances
# ------------------------------------------------------------

CREATE TABLE `attendances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT '1',
  `total_time` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table clx_integrations
# ------------------------------------------------------------

CREATE TABLE `clx_integrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




# Dump of table clx_shared_preferences
# ------------------------------------------------------------

CREATE TABLE `clx_shared_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `relation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relation_id` int(10) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table credit_cards
# ------------------------------------------------------------

CREATE TABLE `credit_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL,
  `card_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_number` char(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_month` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_year` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cvv` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `credit_cards` (`id`, `contact_id`, `card_type`, `card_holder_name`, `card_number`, `expiry_month`, `expiry_year`, `cvv`, `created_at`, `updated_at`)
VALUES
	(1,294,'','Maria Elizabeth','424242424242424242','07','22','456','2018-04-23 07:17:45','2018-04-23 11:21:36');




# Dump of table crm_accounts
# ------------------------------------------------------------

CREATE TABLE `crm_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(200) DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `business_number` varchar(200) DEFAULT NULL,
  `jobtitle` varchar(100) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `o` int(11) DEFAULT '0',
  `phone` varchar(100) DEFAULT NULL,
  `fax` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_primary_contact` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `balance` decimal(16,2) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `notes` text,
  `options` text,
  `tags` text,
  `password` text,
  `token` text,
  `ts` text,
  `img` varchar(100) DEFAULT NULL,
  `web` varchar(200) DEFAULT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `google` varchar(100) DEFAULT NULL,
  `linkedin` varchar(100) DEFAULT NULL,
  `twitter` varchar(100) DEFAULT NULL,
  `skype` varchar(100) DEFAULT NULL,
  `tax_number` varchar(100) DEFAULT NULL,
  `entity_number` varchar(100) DEFAULT NULL,
  `currency` int(11) DEFAULT '0',
  `pmethod` varchar(100) DEFAULT NULL,
  `autologin` varchar(100) DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `lastloginip` varchar(100) DEFAULT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `isp` varchar(100) DEFAULT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `gname` varchar(200) DEFAULT NULL,
  `gid` int(11) NOT NULL DEFAULT '0',
  `sid` varchar(200) DEFAULT NULL,
  `role` varchar(200) DEFAULT NULL,
  `country_code` varchar(20) DEFAULT NULL,
  `country_idd` varchar(20) DEFAULT NULL,
  `signed_up_by` varchar(100) DEFAULT NULL,
  `signed_up_ip` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `ct` varchar(200) DEFAULT NULL,
  `assistant` varchar(200) DEFAULT NULL,
  `asst_phone` varchar(100) DEFAULT NULL,
  `second_email` varchar(100) DEFAULT NULL,
  `second_phone` varchar(100) DEFAULT NULL,
  `taxexempt` varchar(50) DEFAULT NULL,
  `latefeeoveride` varchar(50) DEFAULT NULL,
  `overideduenotices` varchar(50) DEFAULT NULL,
  `separateinvoices` varchar(50) DEFAULT NULL,
  `disableautocc` varchar(50) DEFAULT NULL,
  `billingcid` int(10) NOT NULL DEFAULT '0',
  `securityqid` int(10) NOT NULL DEFAULT '0',
  `securityqans` text,
  `cardtype` varchar(200) DEFAULT NULL,
  `cardlastfour` varchar(20) DEFAULT NULL,
  `cardnum` text,
  `startdate` varchar(50) DEFAULT NULL,
  `expdate` varchar(50) DEFAULT NULL,
  `issuenumber` varchar(200) DEFAULT NULL,
  `bankname` varchar(200) DEFAULT NULL,
  `banktype` varchar(200) DEFAULT NULL,
  `bankcode` varchar(200) DEFAULT NULL,
  `bankacct` varchar(200) DEFAULT NULL,
  `gatewayid` int(10) NOT NULL DEFAULT '0',
  `language` text,
  `pwresetkey` varchar(100) DEFAULT NULL,
  `emailoptout` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `pwresetexpiry` datetime DEFAULT NULL,
  `is_email_verified` int(1) NOT NULL DEFAULT '0',
  `is_phone_veirifed` int(1) NOT NULL DEFAULT '0',
  `photo_id_type` varchar(100) DEFAULT NULL,
  `photo_id` varchar(100) DEFAULT NULL,
  `type` varchar(200) NOT NULL DEFAULT 'Customer',
  `drive` varchar(50) DEFAULT NULL,
  `workspace_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `code` varchar(100) DEFAULT NULL,
  `display_name` varchar(200) DEFAULT NULL,
  `secondary_email` varchar(200) DEFAULT NULL,
  `secondary_phone` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table crm_customfields
# ------------------------------------------------------------

CREATE TABLE `crm_customfields` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ctype` text,
  `relid` int(10) NOT NULL DEFAULT '0',
  `fieldname` text,
  `fieldtype` text,
  `description` text,
  `fieldoptions` text,
  `regexpr` text,
  `adminonly` text,
  `required` text,
  `showorder` text,
  `showinvoice` text,
  `sorder` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table crm_customfieldsvalues
# ------------------------------------------------------------

CREATE TABLE `crm_customfieldsvalues` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `fieldid` int(10) NOT NULL,
  `relid` int(10) NOT NULL,
  `fvalue` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table crm_groups
# ------------------------------------------------------------

CREATE TABLE `crm_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gname` varchar(200) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `discount` varchar(50) DEFAULT NULL,
  `parent` varchar(200) DEFAULT NULL,
  `pid` int(10) DEFAULT NULL,
  `exempt` text,
  `description` text,
  `separateinvoices` text,
  `sorder` int(10) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table crm_industries
# ------------------------------------------------------------

CREATE TABLE `crm_industries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `industry` varchar(200) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  `is_default` int(1) NOT NULL DEFAULT '0',
  `sorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `crm_industries` (`id`, `industry`, `is_active`, `is_default`, `sorder`)
VALUES
	(1,'Agriculture',1,0,0),
	(2,'Apparel',1,0,0),
	(3,'Banking',1,0,0),
	(4,'Biotechnology',1,0,0),
	(5,'Chemicals',1,0,0),
	(6,'Communications',1,0,0),
	(7,'Construction',1,0,0),
	(8,'Consulting',1,0,0),
	(9,'Education',1,0,0),
	(10,'Electronics',1,0,0),
	(11,'Energy',1,0,0),
	(12,'Engineering',1,0,0),
	(13,'Entertainment',1,0,0),
	(14,'Environmental',1,0,0),
	(15,'Finance',1,0,0),
	(16,'Food & Beverage',1,0,0),
	(17,'Government',1,0,0),
	(18,'Healthcare',1,0,0),
	(19,'Hospitality',1,0,0),
	(20,'Insurance',1,0,0),
	(21,'Machinery',1,0,0),
	(22,'Manufacturing',1,0,0),
	(23,'Media',1,0,0),
	(24,'Not For Profit',1,0,0),
	(25,'Other',1,0,0),
	(26,'Recreation',1,0,0),
	(27,'Retail',1,0,0),
	(28,'Shipping',1,0,0),
	(29,'Technology',1,0,0),
	(30,'Telecommunications',1,0,0),
	(31,'Transportation',1,0,0),
	(32,'Utilities',1,0,0);


# Dump of table crm_lead_sources
# ------------------------------------------------------------

CREATE TABLE `crm_lead_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sname` varchar(200) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  `is_default` int(1) NOT NULL DEFAULT '1',
  `sorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `crm_lead_sources` (`id`, `sname`, `is_active`, `is_default`, `sorder`)
VALUES
	(1,'Advertisement',1,1,0),
	(2,'Customer Event',1,1,0),
	(3,'Employee Referral',1,1,0),
	(4,'Google AdWords',1,1,0),
	(5,'Other',1,1,0),
	(6,'Partner',1,1,0),
	(7,'Purchased List',1,1,0),
	(8,'Trade Show',1,1,0),
	(9,'Webinar',1,1,0),
	(10,'Website',1,1,0),
	(11,'Facebook',1,1,0);


# Dump of table crm_lead_status
# ------------------------------------------------------------

CREATE TABLE `crm_lead_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sname` varchar(200) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  `is_default` int(1) NOT NULL DEFAULT '0',
  `is_converted` int(1) NOT NULL DEFAULT '0',
  `sorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `crm_lead_status` (`id`, `sname`, `is_active`, `is_default`, `is_converted`, `sorder`)
VALUES
	(1,'Unqualified',1,0,0,0),
	(2,'New',1,1,0,0),
	(3,'Working',1,0,0,0),
	(4,'Nurturing',1,0,0,0),
	(5,'Qualified',1,0,0,0);


# Dump of table crm_leads
# ------------------------------------------------------------

CREATE TABLE `crm_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secret` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `o` varchar(200) DEFAULT NULL,
  `oid` int(11) NOT NULL DEFAULT '0',
  `salutation` varchar(200) DEFAULT NULL,
  `first_name` varchar(200) DEFAULT NULL,
  `middle_name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT NULL,
  `suffix` varchar(200) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `company_id` int(11) NOT NULL DEFAULT '0',
  `website` varchar(200) DEFAULT NULL,
  `industry` varchar(200) DEFAULT NULL,
  `employees` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `source` varchar(200) DEFAULT NULL,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `added_from` varchar(200) DEFAULT NULL,
  `mobile` varchar(200) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `street` varchar(200) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `zip` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `viewed_at` datetime DEFAULT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `iid` int(11) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `sorder` int(11) NOT NULL DEFAULT '0',
  `assigned` int(11) NOT NULL DEFAULT '0',
  `last_contact` datetime DEFAULT NULL,
  `last_contact_by` varchar(200) DEFAULT NULL,
  `date_converted` datetime DEFAULT NULL,
  `public` int(1) NOT NULL DEFAULT '0',
  `ratings` varchar(50) DEFAULT NULL,
  `flag` int(1) NOT NULL DEFAULT '0',
  `lost` int(1) NOT NULL DEFAULT '0',
  `junk` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `memo` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table crm_salutations
# ------------------------------------------------------------

CREATE TABLE `crm_salutations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sname` varchar(200) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  `is_default` int(1) NOT NULL DEFAULT '0',
  `sorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `crm_salutations` (`id`, `sname`, `is_active`, `is_default`, `sorder`)
VALUES
	(1,'Mr.',1,0,0),
	(2,'Ms.',1,0,0),
	(3,'Mrs.',1,0,0),
	(4,'Dr.',1,0,0),
	(5,'Prof.',1,0,0);


# Dump of table employees
# ------------------------------------------------------------

CREATE TABLE `employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `manager_id` int(10) unsigned DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(16,8) NOT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_name_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_name_first` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_name_mi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_name_last` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banking_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birht` date DEFAULT NULL,
  `marital_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_citizen` tinyint(1) NOT NULL DEFAULT '1',
  `ethnicity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_i9_form` tinyint(1) DEFAULT NULL,
  `work_authorization_expires` date DEFAULT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_fax` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cc_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relation_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relation_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_day_worked` date DEFAULT NULL,
  `last_day_on_benefits` date DEFAULT NULL,
  `last_day_on_payroll` date DEFAULT NULL,
  `termination_type` date DEFAULT NULL,
  `termination_reason` date DEFAULT NULL,
  `is_recommended` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employees` (`id`, `name`, `job_title`, `date_hired`, `department_id`, `manager_id`, `image`, `pay_frequency`, `currency`, `amount`, `employee_id`, `legal_name_title`, `legal_name_first`, `legal_name_mi`, `legal_name_last`, `banking_name`, `ssn`, `gender`, `date_of_birht`, `marital_status`, `is_citizen`, `ethnicity`, `has_i9_form`, `work_authorization_expires`, `address_line_1`, `address_line_2`, `city`, `state`, `zip`, `country`, `phone`, `email`, `work_phone`, `work_mobile`, `work_fax`, `cc_email`, `other`, `emergency_contact_name_1`, `emergency_contact_phone_1`, `emergency_contact_relation_1`, `emergency_contact_name_2`, `emergency_contact_phone_2`, `emergency_contact_relation_2`, `last_day_worked`, `last_day_on_benefits`, `last_day_on_payroll`, `termination_type`, `termination_reason`, `is_recommended`, `is_active`, `facebook`, `google`, `linkedin`, `skype`, `twitter`, `summary`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	(2,'Jakub Swierczak','Customer Success Manager','2018-11-16',NULL,NULL,NULL,'Monthly','USD',7500.00000000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,'1101 Marina Villae Parkway, Suite 201',NULL,'Alameda','California','94501','United States','(650) 488-7772','Jakub@cloudonex.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'',NULL,'',NULL,'','His work motto is “If our customers are happy it means I’m doing a good job”. In his free time he’s improving his guitar skills and beginning his running adventure, hoping one day he will cross a marathon finish line.',NULL,'2018-11-16 05:12:15','2018-11-16 05:12:15');


# Dump of table end_users
# ------------------------------------------------------------

CREATE TABLE `end_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table expense_types
# ------------------------------------------------------------

CREATE TABLE `expense_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sorder` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table ib_assets
# ------------------------------------------------------------

CREATE TABLE `ib_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(200) DEFAULT NULL,
  `price` decimal(14,2) NOT NULL DEFAULT '0.00',
  `date_purchased` date DEFAULT NULL,
  `warranty_period` date DEFAULT NULL,
  `image` text,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ib_doc_rel
# ------------------------------------------------------------

CREATE TABLE `ib_doc_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rtype` varchar(100) NOT NULL DEFAULT 'contact',
  `rid` int(11) NOT NULL DEFAULT '0',
  `did` int(11) NOT NULL DEFAULT '0',
  `can_download` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ib_invoice_access_log
# ------------------------------------------------------------

CREATE TABLE `ib_invoice_access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lid` int(11) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  `iid` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `customer` varchar(200) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `browser` varchar(200) DEFAULT NULL,
  `referer` varchar(200) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `postal_code` varchar(50) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `country_iso` varchar(20) DEFAULT NULL,
  `viewed_at` varchar(200) DEFAULT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ib_kb
# ------------------------------------------------------------

CREATE TABLE `ib_kb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT NULL,
  `gname` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `groups` text,
  `title` text,
  `slug` text,
  `description` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `is_public` int(1) NOT NULL DEFAULT '1',
  `sorder` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ib_kb_groups
# ------------------------------------------------------------

CREATE TABLE `ib_kb_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gname` varchar(200) DEFAULT NULL,
  `description` text,
  `status` varchar(200) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `sorder` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ib_kb_rel
# ------------------------------------------------------------

CREATE TABLE `ib_kb_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kbid` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ib_kb_replies
# ------------------------------------------------------------

CREATE TABLE `ib_kb_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kbid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `reply` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table item_categories
# ------------------------------------------------------------

CREATE TABLE `item_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table order_items
# ------------------------------------------------------------

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `tax_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `tax_name` varchar(200) DEFAULT NULL,
  `currency_symbol` varchar(20) DEFAULT NULL,
  `quantity` varchar(20) DEFAULT NULL,
  `unit_price` decimal(16,2) DEFAULT NULL,
  `tax_rate` decimal(16,3) DEFAULT NULL,
  `total` decimal(16,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table relations
# ------------------------------------------------------------

CREATE TABLE `relations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table sys_accounts
# ------------------------------------------------------------

CREATE TABLE `sys_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `bank_name` varchar(200) DEFAULT NULL,
  `account_number` varchar(200) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `branch` varchar(200) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_person` varchar(200) DEFAULT NULL,
  `contact_phone` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `ib_url` varchar(200) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `notes` text,
  `sorder` int(11) DEFAULT NULL,
  `e` varchar(200) DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `owner_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_activity
# ------------------------------------------------------------

CREATE TABLE `sys_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT '0',
  `msg` text NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT '',
  `stime` varchar(50) NOT NULL,
  `sdate` date NOT NULL,
  `o` int(11) NOT NULL DEFAULT '0',
  `oname` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_api
# ------------------------------------------------------------

CREATE TABLE `sys_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` text,
  `ip` text,
  `apikey` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_canned_responses
# ------------------------------------------------------------

CREATE TABLE `sys_canned_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `message` text,
  `tags` text,
  `attachments` text,
  `sorder` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_cart
# ------------------------------------------------------------

CREATE TABLE `sys_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secret` varchar(100) DEFAULT NULL,
  `items` text,
  `total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `ip` varchar(100) DEFAULT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `browser` varchar(200) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `currency` varchar(200) DEFAULT NULL,
  `language` varchar(200) DEFAULT NULL,
  `coupon` varchar(200) DEFAULT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lon` varchar(50) DEFAULT NULL,
  `item_count` int(11) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `lid` int(11) NOT NULL DEFAULT '0',
  `currency_id` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `expiry` datetime DEFAULT NULL,
  `memo` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_cats
# ------------------------------------------------------------

CREATE TABLE `sys_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `type` enum('Income','Expense') NOT NULL,
  `sorder` int(11) NOT NULL DEFAULT '0',
  `total_amount` decimal(16,4) DEFAULT '0.0000',
  `budget` decimal(16,4) DEFAULT '0.0000',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `total_spend` decimal(16,4) DEFAULT '0.0000',
  `current_month_spend` decimal(16,4) DEFAULT '0.0000',
  `current_year_spend` decimal(16,4) DEFAULT '0.0000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_cats` (`id`, `name`, `type`, `sorder`, `total_amount`, `budget`, `created_at`, `updated_at`, `total_spend`, `current_month_spend`, `current_year_spend`)
VALUES
	(14,'Advertising','Expense',1,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(15,'Bank and Credit Card Interest','Expense',23,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(16,'Car and Truck','Expense',24,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(17,'Commissions and Fees','Expense',25,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(18,'Contract Labor','Expense',26,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(19,'Contributions','Expense',27,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(20,'Cost of Goods Sold','Expense',28,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(21,'Credit Card Interest','Expense',29,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(22,'Depreciation','Expense',31,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(23,'Dividend Payments','Expense',32,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(24,'Employee Benefit Programs','Expense',33,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(25,'Entertainment','Expense',34,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(26,'Gift','Expense',35,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(27,'Insurance','Expense',36,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(28,'Legal, Accountant &amp; Other Professional Services','Expense',37,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(29,'Meals','Expense',38,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(30,'Mortgage Interest','Expense',39,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(31,'Non-Deductible Expense','Expense',40,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(33,'Other Business Property Leasing','Expense',22,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(34,'Owner Draws','Expense',21,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(35,'Payroll Taxes','Expense',8,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(37,'Phone','Expense',9,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(38,'Postage','Expense',10,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(39,'Rent','Expense',12,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(40,'Repairs &amp; Maintenance','Expense',11,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(41,'Supplies','Expense',13,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(42,'Taxes and Licenses','Expense',14,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(43,'Transfer Funds','Expense',15,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(44,'Travel','Expense',16,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(45,'Utilities','Expense',17,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(46,'Vehicle, Machinery &amp; Equipment Rental or Leasing','Expense',18,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(47,'Wages','Expense',19,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(48,'Regular Income','Income',1,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(49,'Owner Contribution','Income',12,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(50,'Interest Income','Income',11,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(51,'Expense Refund','Income',10,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(52,'Other Income','Income',9,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(53,'Salary','Income',8,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(54,'Equities','Income',7,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(55,'Rent &amp; Royalties','Income',6,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(56,'Home equity','Income',5,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(57,'Part Time Work','Income',3,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(58,'Account Transfer','Income',4,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(60,'Health Care','Expense',20,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(63,'Loans','Expense',30,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(64,'Selling Software','Income',2,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(65,'Software Customization','Income',13,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(67,'Salary','Expense',7,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(68,'Paypal','Expense',6,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(69,'Office Equipment','Expense',5,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(70,'Staff Entertaining','Expense',3,0.0000,0.0000,NULL,'2018-11-20 16:57:47',0.0000,0.0000,0.0000),
	(71,'Uncategorized','Income',0,0.0000,0.0000,'2018-04-05 04:59:50','2018-11-20 16:57:47',0.0000,0.0000,0.0000);


# Dump of table sys_companies
# ------------------------------------------------------------

CREATE TABLE `sys_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(200) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `business_number` varchar(200) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `logo_url` varchar(200) DEFAULT NULL,
  `logo_path` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `emails` text,
  `phones` text,
  `tags` text,
  `description` text,
  `notes` text,
  `address1` varchar(200) DEFAULT NULL,
  `address2` varchar(200) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `zip` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `source` varchar(200) DEFAULT NULL,
  `added_from` varchar(200) DEFAULT NULL,
  `o` varchar(200) DEFAULT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `oid` int(11) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `assigned` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `last_contact` datetime DEFAULT NULL,
  `last_contact_by` varchar(200) DEFAULT NULL,
  `ratings` varchar(50) DEFAULT NULL,
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `c1` text,
  `c2` text,
  `c3` text,
  `c4` text,
  `c5` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_currencies
# ------------------------------------------------------------

CREATE TABLE `sys_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(100) DEFAULT NULL,
  `iso_code` varchar(10) DEFAULT NULL,
  `symbol` varchar(20) DEFAULT NULL,
  `rate` decimal(16,8) NOT NULL DEFAULT '1.00000000',
  `prefix` varchar(20) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `format` varchar(100) DEFAULT NULL,
  `decimal_separator` varchar(10) DEFAULT NULL,
  `thousand_separator` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `available_in` text,
  `isdefault` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_currencies` (`id`, `cname`, `iso_code`, `symbol`, `rate`, `prefix`, `suffix`, `format`, `decimal_separator`, `thousand_separator`, `created_at`, `created_by`, `updated_at`, `updated_by`, `available_in`, `isdefault`, `trash`, `archived`)
VALUES
	(1,'USD','USD','$',1.00000000,NULL,NULL,NULL,NULL,NULL,'2018-11-20 16:57:47',NULL,'2018-11-20 16:57:47',NULL,NULL,0,0,0);


# Dump of table sys_documents
# ------------------------------------------------------------

CREATE TABLE `sys_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `related_to` varchar(100) DEFAULT NULL,
  `relation_id` int(11) NOT NULL DEFAULT '0',
  `file_o_name` varchar(200) DEFAULT NULL,
  `file_r_name` varchar(200) DEFAULT NULL,
  `file_mime_type` varchar(200) DEFAULT NULL,
  `file_path` varchar(200) DEFAULT NULL,
  `file_dl_token` varchar(200) DEFAULT NULL,
  `file_owner` int(11) NOT NULL DEFAULT '0',
  `version` varchar(100) DEFAULT NULL,
  `link` varchar(100) DEFAULT NULL,
  `sha1` varchar(40) DEFAULT NULL,
  `md5` varchar(32) DEFAULT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `contacts` text,
  `deals` text,
  `leads` text,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `customer_can_download` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `is_global` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_email_logs
# ------------------------------------------------------------

CREATE TABLE `sys_email_logs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `sender` varchar(200) NOT NULL,
  `email` text NOT NULL,
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `date` datetime DEFAULT NULL,
  `iid` int(11) NOT NULL DEFAULT '0',
  `rel_type` varchar(100) DEFAULT NULL,
  `rel_id` int(11) NOT NULL DEFAULT '0',
  `purchase_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_email_templates
# ------------------------------------------------------------

CREATE TABLE `sys_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tplname` varchar(128) NOT NULL,
  `language_id` int(11) NOT NULL DEFAULT '1',
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `send` varchar(50) DEFAULT 'Active',
  `core` enum('Yes','No') DEFAULT 'Yes',
  `hidden` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`,`language_id`),
  KEY `tplname` (`tplname`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_email_templates` (`id`, `tplname`, `language_id`, `subject`, `message`, `send`, `core`, `hidden`)
VALUES
	(3,'Invoice:Invoice Created',1,'{{business_name}} Invoice','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\"><div style=\"padding:5px;font-size:11pt;font-weight:bold\">   Greetings,</div>	<div style=\"padding:5px\">		This email serves as your official invoice from <strong>{{business_name}}. </strong>	</div><div style=\"padding:10px 5px\">    Invoice URL: <a href=\"{{invoice_url}}\" target=\"_blank\">{{invoice_url}}</a><a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{app_url}}\"></a><br>Invoice ID: {{invoice_id}}<br>Invoice Amount: {{invoice_amount}}<br>Due Date: {{invoice_due_date}}</div><div style=\"padding:5px\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">If you have any questions or need assistance, please don\'t hesitate to contact us.</span><br></div><div style=\"padding:0px 5px\">	<div>Best Regards,<br>{{business_name}} Team</div></div></div>','Yes','Yes',0),
	(7,'Admin:Password Change Request',1,'{{business_name}} password change request','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\"><div style=\"padding:5px;font-size:11pt;font-weight:bold\">   Hi {{name}},</div>	<div style=\"padding:5px\">		This is to confirm that we have received a Forgot Password request for your Account Username - {{username}} <br>From the IP Address - {{ip_address}}	</div>	<div style=\"padding:5px\">		Click this linke to reset your password- <br><a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{password_reset_link}}\">{{password_reset_link}}</a>	</div><div style=\"padding:5px\">Please note: until your password has been changed, your current password will remain valid. The Forgot Password Link will be available for a limited time only.</div><div style=\"padding:0px 5px\">	<div>Best Regards,<br>{{business_name}} Team</div></div></div>','Yes','Yes',0),
	(10,'Admin:New Password',1,'{{business_name}} New Password for Admin','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\">\n\n<div style=\"padding:5px;font-size:11pt;font-weight:bold\">\n   Hello {{name}}\n</div>\n\n\n	<div style=\"padding:5px\">\n		Here is your new password for <strong>{{business_name}}. </strong>\n	</div>\n\n	\n<div style=\"padding:10px 5px\">\n    Log in URL: <a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{login_url}}\">{{login_url}}</a><br>Username: {{username}}<br>Password: {{password}}</div>\n\n<div style=\"padding:5px\">For security reason, Please change your password after login. </div>\n\n<div style=\"padding:0px 5px\">\n	<div>Best Regards,<br>{{business_name}} Team</div>\n\n</div>\n\n</div>','Yes','Yes',0),
	(12,'Invoice:Invoice Payment Reminder',1,'{{business_name}} Invoice Payment Reminder','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\"><div style=\"padding:5px;font-size:11pt;font-weight:bold\">   Greetings,</div>	<div style=\"padding:5px\">		This is a billing reminder that your invoice no. {{invoice_id}} which was generated on {{invoice_date}} is due on {{invoice_due_date}}. 	</div><div style=\"padding:10px 5px\">    Invoice URL: <a href=\"{{invoice_url}}\" target=\"_blank\">{{invoice_url}}</a><a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{app_url}}\"></a><br>Invoice ID: {{invoice_id}}<br>Invoice Amount: {{invoice_amount}}<br>Due Date: {{invoice_due_date}}</div><div style=\"padding:5px\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">If you have any questions or need assistance, please don\'t hesitate to contact us.</span><br></div><div style=\"padding:0px 5px\">	<div>Best Regards,<br>{{business_name}} Team</div></div></div>','Yes','Yes',0),
	(13,'Invoice:Invoice Overdue Notice',1,'{{business_name}} Invoice Overdue Notice','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\"><div style=\"padding:5px;font-size:11pt;font-weight:bold\">   Greetings,</div>	<div style=\"padding:5px\">		This is the notice that your invoice no. {{invoice_id}} which was generated on {{invoice_date}} is now overdue.	</div>	<div style=\"padding:10px 5px\">    Invoice URL: <a href=\"{{invoice_url}}\" target=\"_blank\">{{invoice_url}}</a><a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{app_url}}\"></a><br>Invoice ID: {{invoice_id}}<br>Invoice Amount: {{invoice_amount}}<br>Due Date: {{invoice_due_date}}</div><div style=\"padding:5px\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">If you have any questions or need assistance, please don\'t hesitate to contact us.</span><br></div><div style=\"padding:0px 5px\">	<div>Best Regards,<br>{{business_name}} Team</div></div></div>','Yes','Yes',0),
	(14,'Invoice:Invoice Payment Confirmation',1,'{{business_name}} Invoice Payment Confirmation','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\">\n\n<div style=\"padding:5px;font-size:11pt;font-weight:bold\">\n   Greetings,\n</div>\n\n\n\n	<div style=\"padding:5px\">\n		This is a payment receipt for Invoice {{invoice_id}} sent on {{invoice_date}}.\n	</div>\n\n\n	<div style=\"padding:5px\">\n		Login to your client Portal to view this invoice.\n	</div>\n\n\n<div style=\"padding:10px 5px\">\n    Invoice URL: <a href=\"{{invoice_url}}\" target=\"_blank\">{{invoice_url}}</a><a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{app_url}}\"></a><br>Invoice ID: {{invoice_id}}<br>Invoice Amount: {{invoice_amount}}<br>Due Date: {{invoice_due_date}}</div>\n\n\n<div style=\"padding:5px\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">If you have any questions or need assistance, please don\'t hesitate to contact us.</span><br></div>\n\n\n<div style=\"padding:0px 5px\">\n	<div>Best Regards,<br>{{business_name}} Team</div>\n\n\n</div>\n\n\n</div>','Yes','Yes',0),
	(15,'Invoice:Invoice Refund Confirmation',1,'{{business_name}} Invoice Refund Confirmation','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,\'droid sans\',\'lucida sans\',sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\"><div style=\"padding:5px;font-size:11pt;font-weight:bold\">   Greetings,</div>	<div style=\"padding:5px\">		This is confirmation that a refund has been processed for Invoice {{invoice_id}} sent on {{invoice_date}}.	</div><div style=\"padding:10px 5px\">    Invoice URL: <a href=\"{{invoice_url}}\" target=\"_blank\">{{invoice_url}}</a><a target=\"_blank\" style=\"color:#1da9c0;font-weight:bold;padding:3px;text-decoration:none\" href=\"{{app_url}}\"></a><br>Invoice ID: {{invoice_id}}<br>Invoice Amount: {{invoice_amount}}<br>Due Date: {{invoice_due_date}}</div><div style=\"padding:5px\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">If you have any questions or need assistance, please don\'t hesitate to contact us.</span><br></div><div style=\"padding:0px 5px\">	<div>Best Regards,<br>{{business_name}} Team</div></div></div>','Yes','Yes',0),
	(16,'Quote:Quote Created',1,'{{quote_subject}}','<div style=\"line-height:1.6;color:#222;text-align:left;width:550px;font-size:10pt;margin:0px 10px;font-family:verdana,sans-serif;padding:14px;border:3px solid #d8d8d8;border-top:3px solid #007bc3\"><div style=\"padding:5px;font-size:11pt;font-weight:bold\">   Greetings,</div>	<div style=\"padding:5px\">		Dear {{contact_name}},&nbsp;<br> Here is the quote you requested for.  The quote is valid until {{valid_until}}.	</div><div style=\"padding:10px 5px\">    Quote Unique URL: <a href=\"{{quote_url}}\" target=\"_blank\">{{quote_url}}</a><br></div><div style=\"padding:5px\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">You may view the quote at any time and simply reply to this email with any further questions or requirement.</span><br></div><div style=\"padding:0px 5px\">	<div>Best Regards,<br>{{business_name}} Team</div></div></div>','Yes','Yes',0),
	(17,'Client:Client Signup Email',1,'Your {{business_name}} Login Info','<p>Dear {{client_name}},</p>\n<p>Welcome to {{business_name}}.</p>\n<p>You can track your billing, profile, transactions from this portal.</p>\n<p>Your login information is as follows:</p>\n<p>---------------------------------------------------------------------------------------</p>\n<p>Login URL: {{client_login_url}} <br />Email Address: {{client_email}}<br /> Password: Your chosen password.</p>\n<p>----------------------------------------------------------------------------------------</p>\n<p>We very much appreciate you for choosing us.</p>\n<p>{{business_name}} Team</p>','Yes','Yes',0),
	(18,'Tickets:Client Ticket Created',1,'{{subject}}','<p>{{client_name}},</p>\n<p>Thank you for contacting our support team. A support ticket has now been opened for your request. You will be notified when a response is made by email. Your ticket ID is {{ticket_id}} and a copy of your original message is included below.</p>\n<p>Subject: {{ticket_subject}} <br /> Message: <br /> {{message}} <br /> Priority: {{ticket_priority}} <br /> Status: {{ticket_status}}</p>\n<p>You can view the ticket at any time at {{ticket_link}}</p>','Yes','Yes',0),
	(19,'Tickets:Admin Ticket Created',1,'{{subject}}','<p>{{admin_view_link}}</p> {{message}}','Yes','Yes',0),
	(20,'Tickets:Client Response',1,'{{subject}}','<p>{{ticket_message}}</p>\n<p>----------------------------------------------<br /> Ticket ID: #{{ticket_id}}<br /> Subject: {{ticket_subject}}<br /> Status: {{ticket_status}}<br /> Ticket URL: {{ticket_link}}<br /> ----------------------------------------------</p>','Yes','Yes',0),
	(21,'Tickets:Admin Response',1,'{{subject}}','<p>{{ticket_message}}</p>\n<p>----------------------------------------------<br /> Ticket ID: #{{ticket_id}}<br /> Subject: {{ticket_subject}}<br /> Status: {{ticket_status}}<br /> Ticket URL: {{ticket_link}}<br /> ----------------------------------------------</p>','Yes','Yes',0),
	(23,'Purchase Invoice:Invoice Created',1,'{{business_name}} Invoice','<div style=\"line-height: 1.6; color: #222; text-align: left; width: 550px; font-size: 10pt; margin: 0px 10px; font-family: verdana,\'droid sans\',\'lucida sans\',sans-serif; padding: 14px; border: 3px solid #d8d8d8; border-top: 3px solid #007bc3;\">\n<div style=\"padding: 5px; font-size: 11pt; font-weight: bold;\">Greetings,</div>\n<div style=\"padding: 5px;\">This email serves as your official invoice from <strong>{{business_name}}. </strong></div>\n<div style=\"padding: 10px 5px;\">Invoice URL: {{invoice_url}}<br />Invoice ID: {{invoice_id}}<br />Invoice Amount: {{invoice_amount}}</div>\n<div style=\"padding: 5px;\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">If you have any questions or need assistance, please don\'t hesitate to contact us.</span></div>\n<div style=\"padding: 0px 5px;\">\n<div>Best Regards,<br />{{business_name}} Team</div>\n</div>\n</div>','Yes','Yes',0),
	(24,'Quote:Quote Cancelled',1,'{{business_name}} Quote','<div style=\"line-height: 1.6; color: #222; text-align: left; width: 550px; font-size: 10pt; margin: 0px 10px; font-family: verdana,sans-serif; padding: 14px; border: 3px solid #d8d8d8; border-top: 3px solid #007bc3;\">\n<div style=\"padding: 5px; font-size: 11pt; font-weight: bold;\">Greetings,</div>\n<div style=\"padding: 5px;\">Dear {{contact_name}},&nbsp;<br />We are sorry to see you go. If you chnage your mind, you can Accept it again from following url. The quote is valid until {{valid_until}}.</div>\n<div style=\"padding: 10px 5px;\">Quote Unique URL: <a href=\"http://stackb.dev/{{quote_url}}\" target=\"_blank\" rel=\"noopener noreferrer\">{{quote_url}}</a></div>\n<div style=\"padding: 5px;\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">You may view the quote at any time and simply reply to this email with any further questions or requirement.</span></div>\n<div style=\"padding: 0px 5px;\">\n<div>Best Regards,<br />{{business_name}} Team</div>\n</div>\n</div>','Yes','Yes',0),
	(25,'Quote:Quote Accepted',1,'{{business_name}} Quote','<div style=\"line-height: 1.6; color: #222; text-align: left; width: 550px; font-size: 10pt; margin: 0px 10px; font-family: verdana,sans-serif; padding: 14px; border: 3px solid #d8d8d8; border-top: 3px solid #007bc3;\">\n<div style=\"padding: 5px; font-size: 11pt; font-weight: bold;\">Greetings,</div>\n<div style=\"padding: 5px;\">Dear {{contact_name}},&nbsp;<br />Thank you for accepting the Quote.</div>\n<div style=\"padding: 10px 5px;\">Quote: <a href=\"{{quote_url}}\" target=\"_blank\" rel=\"noopener noreferrer\">{{quote_url}}</a></div>\n<div style=\"padding: 5px;\"><span style=\"font-size: 13.3333330154419px; line-height: 21.3333320617676px;\">You may view the quote at any time and simply reply to this email with any further questions or requirement.</span></div>\n<div style=\"padding: 0px 5px;\">\n<div>Best Regards,<br />{{business_name}} Team</div>\n</div>\n</div>','Yes','Yes',0);


# Dump of table sys_emailconfig
# ------------------------------------------------------------

CREATE TABLE `sys_emailconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(50) NOT NULL,
  `host` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `apikey` varchar(200) NOT NULL,
  `port` varchar(10) NOT NULL,
  `secure` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_emailconfig` (`id`, `method`, `host`, `username`, `password`, `apikey`, `port`, `secure`)
VALUES
	(1,'phpmail','','','','','','');


# Dump of table sys_events
# ------------------------------------------------------------

CREATE TABLE `sys_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `description` text,
  `contacts` text,
  `deals` text,
  `owner` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `etype` varchar(200) DEFAULT NULL,
  `priority` varchar(200) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `o` varchar(200) DEFAULT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `iid` int(11) NOT NULL DEFAULT '0',
  `oid` int(11) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `allday` int(1) NOT NULL DEFAULT '0',
  `notification` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_invoiceitems
# ------------------------------------------------------------

CREATE TABLE `sys_invoiceitems` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `invoiceid` int(10) NOT NULL DEFAULT '0',
  `userid` int(10) NOT NULL,
  `type` text NOT NULL,
  `relid` int(10) NOT NULL,
  `itemcode` varchar(100) NOT NULL,
  `tax_code` varchar(200) DEFAULT NULL,
  `description` text NOT NULL,
  `qty` varchar(20) NOT NULL DEFAULT '1',
  `amount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `taxed` int(1) NOT NULL,
  `tax_name` varchar(200) DEFAULT NULL,
  `tax_rate` decimal(16,3) DEFAULT NULL,
  `taxamount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax1` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `tax2` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `tax3` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `discount_type` varchar(100) DEFAULT NULL,
  `discount_amount` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `duedate` date DEFAULT NULL,
  `paymentmethod` text NOT NULL,
  `notes` text NOT NULL,
  `business_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoiceid` (`invoiceid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table sys_invoices
# ------------------------------------------------------------

CREATE TABLE `sys_invoices` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `type` varchar(100) DEFAULT 'Invoice',
  `related_to` varchar(100) DEFAULT NULL,
  `relation_id` int(11) NOT NULL DEFAULT '0',
  `account` varchar(200) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `cn` varchar(100) NOT NULL DEFAULT '',
  `invoicenum` text NOT NULL,
  `date` date DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `datepaid` datetime DEFAULT NULL,
  `subtotal` decimal(18,2) NOT NULL,
  `discount_type` varchar(1) NOT NULL DEFAULT 'f',
  `discount_value` decimal(14,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `taxname` varchar(100) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `tax2` decimal(10,2) NOT NULL,
  `tax_total` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `taxrate` decimal(10,2) NOT NULL,
  `taxrate2` decimal(10,2) NOT NULL,
  `status` text NOT NULL,
  `paymentmethod` text NOT NULL,
  `notes` text NOT NULL,
  `vtoken` varchar(20) NOT NULL,
  `ptoken` varchar(20) NOT NULL,
  `r` varchar(100) NOT NULL DEFAULT '0',
  `nd` date DEFAULT NULL,
  `eid` int(10) NOT NULL DEFAULT '0',
  `ename` varchar(200) NOT NULL DEFAULT '',
  `vid` int(11) NOT NULL DEFAULT '0',
  `quote_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) DEFAULT '0',
  `currency` int(11) NOT NULL DEFAULT '0',
  `currency_iso_code` char(3) DEFAULT NULL,
  `currency_symbol` varchar(10) DEFAULT NULL,
  `currency_prefix` varchar(10) DEFAULT NULL,
  `currency_suffix` varchar(10) DEFAULT NULL,
  `currency_rate` decimal(11,8) NOT NULL DEFAULT '1.0000',
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurring_ends` date DEFAULT NULL,
  `last_recurring_date` date DEFAULT NULL,
  `source` varchar(200) DEFAULT NULL,
  `sale_agent` int(11) NOT NULL DEFAULT '0',
  `last_overdue_reminder` date DEFAULT NULL,
  `allowed_payment_methods` text,
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(50) DEFAULT NULL,
  `billing_country` varchar(100) DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `q_hide` tinyint(1) NOT NULL DEFAULT '0',
  `show_quantity_as` varchar(100) DEFAULT NULL,
  `pid` int(11) NOT NULL DEFAULT '0',
  `is_credit_invoice` int(1) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `aname` varchar(200) DEFAULT NULL,
  `receipt_number` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `workspace_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `c1` varchar(255) DEFAULT NULL,
  `c2` varchar(255) DEFAULT NULL,
  `c3` varchar(255) DEFAULT NULL,
  `c4` varchar(255) DEFAULT NULL,
  `c5` text,
  `signature_data_source` text,
  `signature_data_base64` text,
  `signature_data_svg` text,
  `is_same_state` tinyint(1) DEFAULT '1',
  `code` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `status` (`status`(3))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table sys_item_cats
# ------------------------------------------------------------

CREATE TABLE `sys_item_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `img` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `description` text,
  `sorder` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_items
# ------------------------------------------------------------

CREATE TABLE `sys_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `unit` varchar(100) NOT NULL DEFAULT '',
  `sales_price` decimal(16,2) NOT NULL DEFAULT '0.00',
  `show_in_catalog` tinyint(1) NOT NULL DEFAULT '0',
  `inventory` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `weight` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `width` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `length` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `height` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `sku` varchar(50) DEFAULT NULL,
  `upc` varchar(50) DEFAULT NULL,
  `ean` varchar(50) DEFAULT NULL,
  `mpn` varchar(50) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `sid` int(11) NOT NULL DEFAULT '0',
  `supplier` varchar(200) DEFAULT NULL,
  `bid` int(11) NOT NULL DEFAULT '0',
  `brand` varchar(200) DEFAULT NULL,
  `sell_account` int(11) NOT NULL DEFAULT '0',
  `purchase_account` int(11) NOT NULL DEFAULT '0',
  `inventory_account` int(11) NOT NULL DEFAULT '0',
  `taxable` int(1) NOT NULL DEFAULT '0',
  `location` varchar(200) DEFAULT NULL,
  `item_number` varchar(100) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `type` enum('Service','Product') NOT NULL,
  `track_inventroy` enum('Yes','No') NOT NULL DEFAULT 'No',
  `negative_stock` enum('Yes','No') NOT NULL DEFAULT 'No',
  `available` int(11) NOT NULL DEFAULT '0',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `added` date DEFAULT NULL,
  `last_sold` date DEFAULT NULL,
  `e` mediumtext NOT NULL,
  `sorder` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `gname` varchar(100) DEFAULT NULL,
  `product_id` varchar(100) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `expire_days` int(11) NOT NULL DEFAULT '0',
  `image` text,
  `flag` int(1) NOT NULL DEFAULT '0',
  `is_service` int(1) NOT NULL DEFAULT '0',
  `commission_percent` decimal(16,2) NOT NULL DEFAULT '0.00',
  `commission_percent_type` varchar(100) DEFAULT NULL,
  `commission_fixed` decimal(16,2) NOT NULL DEFAULT '0.00',
  `trash` int(1) NOT NULL DEFAULT '0',
  `payterm` varchar(200) DEFAULT NULL,
  `cost_price` decimal(16,2) NOT NULL DEFAULT '0.00',
  `unit_price` decimal(16,2) NOT NULL DEFAULT '0.00',
  `promo_price` decimal(16,2) NOT NULL DEFAULT '0.00',
  `setup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `onetime` decimal(16,2) NOT NULL DEFAULT '0.00',
  `monthly` decimal(16,2) NOT NULL DEFAULT '0.00',
  `monthlysetup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `quarterly` decimal(16,2) NOT NULL DEFAULT '0.00',
  `quarterlysetup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `halfyearly` decimal(16,2) NOT NULL DEFAULT '0.00',
  `halfyearlysetup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `annually` decimal(16,2) NOT NULL DEFAULT '0.00',
  `annuallysetup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `biennially` decimal(16,2) NOT NULL DEFAULT '0.00',
  `bienniallysetup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `triennially` decimal(16,2) NOT NULL DEFAULT '0.00',
  `trienniallysetup` decimal(16,2) NOT NULL DEFAULT '0.00',
  `has_domain` varchar(100) DEFAULT NULL,
  `free_domain` varchar(100) DEFAULT NULL,
  `email_rel` int(11) NOT NULL DEFAULT '0',
  `tags` text,
  `sold_count` decimal(16,4) DEFAULT '0.0000',
  `total_amount` decimal(16,4) DEFAULT '0.0000',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `tax_code` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_leads
# ------------------------------------------------------------

CREATE TABLE `sys_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(200) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `source` varchar(200) DEFAULT NULL,
  `added_from` varchar(200) DEFAULT NULL,
  `o` varchar(200) DEFAULT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `iid` int(11) NOT NULL DEFAULT '0',
  `oid` int(11) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `sorder` int(11) NOT NULL DEFAULT '0',
  `assigned` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `last_contact` datetime DEFAULT NULL,
  `last_contact_by` varchar(200) DEFAULT NULL,
  `date_converted` datetime DEFAULT NULL,
  `public` int(1) NOT NULL DEFAULT '0',
  `ratings` varchar(50) DEFAULT NULL,
  `flag` int(1) NOT NULL DEFAULT '0',
  `lost` int(1) NOT NULL DEFAULT '0',
  `junk` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_logs
# ------------------------------------------------------------

CREATE TABLE `sys_logs` (
                            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                            `date` datetime DEFAULT NULL,
                            `type` varchar(50) NOT NULL,
                            `description` text NOT NULL,
                            `userid` int(10) NOT NULL,
                            `ip` text NOT NULL,
                            `related_to` varchar(100) DEFAULT NULL,
                            `related_id` int(10) DEFAULT NULL,
                            PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table sys_orders
# ------------------------------------------------------------

CREATE TABLE `sys_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordernum` varchar(50) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `sales_person` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `cname` varchar(100) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `bid` int(11) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `date_expiry` date DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `stitle` varchar(200) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `iid` int(11) DEFAULT NULL,
  `aid` int(11) DEFAULT NULL,
  `amount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `recurring` decimal(16,2) NOT NULL DEFAULT '0.00',
  `setup_fee` decimal(16,2) NOT NULL DEFAULT '0.00',
  `billing_cycle` text,
  `addon_ids` text,
  `related_orders` text,
  `description` text,
  `upgrade_ids` text,
  `xdata` text,
  `xsecret` varchar(100) DEFAULT NULL,
  `promo_code` text,
  `promo_type` text,
  `promo_value` text,
  `payment_method` text,
  `ipaddress` text,
  `fraud_module` text,
  `fraud_output` text,
  `activation_subject` text,
  `activation_message` text,
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `c1` text,
  `c2` text,
  `c3` text,
  `c4` text,
  `c5` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_permissions
# ------------------------------------------------------------

CREATE TABLE `sys_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pname` varchar(200) DEFAULT NULL,
  `shortname` varchar(200) DEFAULT NULL,
  `available` int(1) NOT NULL DEFAULT '0',
  `core` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table sys_pg
# ------------------------------------------------------------

CREATE TABLE `sys_pg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `settings` text NOT NULL,
  `value` text NOT NULL,
  `processor` text NOT NULL,
  `ins` text NOT NULL,
  `c1` text NOT NULL,
  `c2` text NOT NULL,
  `c3` text NOT NULL,
  `c4` text NOT NULL,
  `c5` text NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `sorder` int(2) NOT NULL,
  `logo` varchar(200) DEFAULT NULL,
  `mode` varchar(200) DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gateway_setting` (`name`(32),`processor`(32)),
  KEY `setting_value` (`processor`(32),`ins`(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_pg` (`id`, `name`, `settings`, `value`, `processor`, `ins`, `c1`, `c2`, `c3`, `c4`, `c5`, `status`, `sorder`, `logo`, `mode`, `account_id`, `created_at`, `updated_at`)
VALUES
	(1,'Paypal','Paypal Email','demo@example.com','paypal','Invoices','USD','1','','','','Inactive',2,NULL,'',NULL,NULL,NULL),
	(2,'Stripe','API Key','sk_test_your_stripe_api_key','stripe','','USD','','','','','Inactive',3,NULL,'',NULL,NULL,NULL),
	(3,'Bank / Cash','Instructions','Make a Payment to Our Bank Account <br>Bank Name: City Bank <br>Account Name: Sadia Sharmin <br>Account Number: 1505XXXXXXXX <br>','manualpayment','','','','','','','Inactive',4,NULL,'',NULL,NULL,NULL),
	(4,'Authorize.net','API_LOGIN_ID','Insert API Login ID here','authorize_net','','Insert Transaction Key Here','','','','','Inactive',5,NULL,'',NULL,NULL,NULL),
	(5,'Braintree','Merchant ID','your merchant id','braintree','','your public key','your private key','bank account','sandbox','','Inactive',6,NULL,NULL,NULL,NULL,NULL);


# Dump of table sys_pl
# ------------------------------------------------------------

CREATE TABLE `sys_pl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `sorder` int(11) NOT NULL DEFAULT '0',
  `build` int(10) DEFAULT '1',
  `c1` text,
  `c2` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_pmethods
# ------------------------------------------------------------

CREATE TABLE `sys_pmethods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_pmethods` (`id`, `name`, `sorder`)
VALUES
	(1,'Cash',1),
	(2,'Check',4),
	(3,'Credit Card',5),
	(4,'Debit',6),
	(5,'Electronic Transfer',7),
	(9,'Paypal',2),
	(10,'ATM Withdrawals',3),
	(11,'Pagseguro',1);






# Dump of table sys_quoteitems
# ------------------------------------------------------------

CREATE TABLE `sys_quoteitems` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `qid` int(10) NOT NULL,
  `itemcode` text NOT NULL,
  `description` text NOT NULL,
  `qty` text NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `total` decimal(18,2) NOT NULL,
  `taxable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_quotes
# ------------------------------------------------------------

CREATE TABLE `sys_quotes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `subject` text NOT NULL,
  `stage` enum('Draft','Delivered','On Hold','Accepted','Lost','Dead') NOT NULL,
  `validuntil` date NOT NULL,
  `userid` int(10) NOT NULL,
  `invoicenum` text NOT NULL,
  `cn` text NOT NULL,
  `account` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `companyname` text NOT NULL,
  `email` text NOT NULL,
  `address1` text NOT NULL,
  `address2` text NOT NULL,
  `city` text NOT NULL,
  `state` text NOT NULL,
  `postcode` text NOT NULL,
  `country` text NOT NULL,
  `phonenumber` text NOT NULL,
  `currency` int(10) NOT NULL,
  `subtotal` decimal(18,2) NOT NULL,
  `discount_type` text NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `taxname` text NOT NULL,
  `taxrate` decimal(10,3) NOT NULL,
  `tax1` decimal(10,3) NOT NULL,
  `tax2` decimal(10,3) NOT NULL,
  `total` decimal(18,2) NOT NULL,
  `proposal` text NOT NULL,
  `customernotes` text NOT NULL,
  `adminnotes` text NOT NULL,
  `datecreated` date NOT NULL,
  `lastmodified` date NOT NULL,
  `datesent` date NOT NULL,
  `dateaccepted` date NOT NULL,
  `vtoken` text NOT NULL,
  `code` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_roles
# ------------------------------------------------------------

CREATE TABLE `sys_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rname` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_roles` (`id`, `rname`)
VALUES
	(3,'Employee');


# Dump of table sys_sales
# ------------------------------------------------------------

CREATE TABLE `sys_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT '0',
  `oid` int(11) NOT NULL DEFAULT '0',
  `oname` varchar(200) NOT NULL,
  `description` mediumtext NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `term` varchar(100) NOT NULL,
  `milestone` varchar(100) NOT NULL,
  `p` int(11) NOT NULL,
  `o` int(11) NOT NULL,
  `open` date NOT NULL,
  `close` date NOT NULL,
  `status` enum('New','In Progress','Won','Lost') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_schedule
# ------------------------------------------------------------

CREATE TABLE `sys_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` mediumtext NOT NULL,
  `val` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_schedule` (`id`, `cname`, `val`)
VALUES
	(1,'accounting_snapshot','Active'),
	(2,'recurring_invoice','Active'),
	(3,'notify','Active'),
	(4,'notifyemail','demo@example.com');


# Dump of table sys_schedulelogs
# ------------------------------------------------------------

CREATE TABLE `sys_schedulelogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `logs` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table sys_staffpermissions
# ------------------------------------------------------------

CREATE TABLE `sys_staffpermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `shortname` varchar(50) DEFAULT NULL,
  `can_view` int(1) NOT NULL DEFAULT '0',
  `can_edit` int(1) NOT NULL DEFAULT '0',
  `can_create` int(1) NOT NULL DEFAULT '0',
  `can_delete` int(1) NOT NULL DEFAULT '0',
  `all_data` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_staffpermissions` (`rid`, `pid`, `shortname`, `can_view`, `can_edit`, `can_create`, `can_delete`, `all_data`)
VALUES
	(3,1,'customers',1,1,1,1,0),
	(3,2,'companies',0,0,0,0,0),
	(3,3,'transactions',0,0,0,0,0),
	(3,4,'sales',0,0,0,0,0),
	(3,4,'cms',0,0,0,0,0),
	(3,5,'bank_n_cash',0,0,0,0,0),
	(3,6,'products_n_services',0,0,0,0,0),
	(3,7,'reports',0,0,0,0,0),
	(3,8,'utilities',0,0,0,0,0),
	(3,9,'appearance',0,0,0,0,0),
	(3,10,'plugins',0,0,0,0,0),
	(3,11,'calendar',0,0,0,0,0),
	(3,12,'leads',0,0,0,0,0),
	(3,13,'tasks',0,0,0,0,0),
	(3,14,'contracts',0,0,0,0,0),
	(3,15,'orders',0,0,0,0,0),
	(3,16,'settings',0,0,0,0,0),
	(3,17,'documents',0,0,0,0,0),
	(3,18,'purchase',0,0,0,0,0),
	(3,19,'suppliers',0,0,0,0,0),
	(3,20,'sms',0,0,0,0,0),
	(3,21,'support',0,0,0,0,0),
	(3,22,'kb',0,0,0,0,0),
	(3,23,'password_manager',0,0,0,0,0),
	(3,24,'hr',0,0,0,0,0);


# Dump of table sys_status
# ------------------------------------------------------------

CREATE TABLE `sys_status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `sorder` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sys_status` (`id`, `type`, `name`, `sorder`, `created_at`, `updated_at`)
VALUES
	(1,'Purchase Invoice','Pending',NULL,NULL,NULL),
	(2,'Purchase Invoice','Accepted',NULL,NULL,NULL),
	(3,'Purchase Invoice','Declined',NULL,NULL,NULL);


# Dump of table sys_tags
# ------------------------------------------------------------

CREATE TABLE `sys_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_tasks
# ------------------------------------------------------------

CREATE TABLE `sys_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `description` text,
  `status` varchar(200) DEFAULT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `oid` int(11) NOT NULL DEFAULT '0',
  `iid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `eid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `did` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `subscribers` text,
  `assigned_to` text,
  `priority` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(200) DEFAULT NULL,
  `vtoken` varchar(50) DEFAULT NULL,
  `ptoken` varchar(50) DEFAULT NULL,
  `started` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `stime` varchar(50) DEFAULT NULL,
  `dtime` varchar(50) DEFAULT NULL,
  `time_spent` varchar(50) DEFAULT NULL,
  `date_finished` date DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `flag` int(1) NOT NULL DEFAULT '0',
  `finished` int(1) NOT NULL DEFAULT '0',
  `ratings` varchar(50) DEFAULT NULL,
  `rel_type` varchar(50) DEFAULT NULL,
  `rel_id` int(11) DEFAULT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `is_public` int(1) NOT NULL DEFAULT '0',
  `billable` int(1) NOT NULL DEFAULT '0',
  `billed` int(1) NOT NULL DEFAULT '0',
  `hourly_rate` decimal(14,2) NOT NULL DEFAULT '0.00',
  `milestone` int(11) DEFAULT NULL,
  `progress` int(3) DEFAULT NULL,
  `visible_to_client` int(1) NOT NULL DEFAULT '0',
  `notification` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_tax
# ------------------------------------------------------------

CREATE TABLE `sys_tax` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` text,
  `state` text,
  `country` text,
  `rate` decimal(10,3) DEFAULT NULL,
  `aid` int(11) DEFAULT NULL,
  `bal` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_default` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state_country` (`state`(32),`country`(2))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sys_tax` (`id`, `name`, `state`, `country`, `rate`, `aid`, `bal`, `created_at`, `updated_at`, `is_default`)
VALUES
	(1,'None',NULL,NULL,0.00,NULL,0.00,'2018-11-20 16:57:47','2018-11-20 16:57:47',1);


# Dump of table sys_ticketdepartments
# ------------------------------------------------------------

CREATE TABLE `sys_ticketdepartments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dname` varchar(200) DEFAULT NULL,
  `description` text,
  `email` varchar(200) DEFAULT NULL,
  `hidden` int(1) NOT NULL DEFAULT '0',
  `delete_after_import` int(1) NOT NULL DEFAULT '0',
  `host` varchar(200) DEFAULT NULL,
  `port` varchar(50) DEFAULT NULL,
  `username` varchar(200) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `encryption` varchar(100) DEFAULT NULL,
  `calendar_id` varchar(200) DEFAULT NULL,
  `sorder` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `sys_ticketdepartments` (`id`, `dname`, `description`, `email`, `hidden`, `delete_after_import`, `host`, `port`, `username`, `password`, `encryption`, `calendar_id`, `sorder`, `created_at`, `updated_at`)
VALUES
	(1,'Sales',NULL,'',0,0,'','','','','no',NULL,1,NULL,NULL),
	(2,'Support',NULL,'',0,0,'','','','','no',NULL,1,NULL,NULL),
	(3,'Billing',NULL,'',0,0,'','','','','no',NULL,1,NULL,NULL);


# Dump of table sys_ticketmaillog
# ------------------------------------------------------------

CREATE TABLE `sys_ticketmaillog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `account` varchar(200) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text,
  `status` varchar(100) DEFAULT NULL,
  `attachments` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_ticketpredefinedreplies
# ------------------------------------------------------------

CREATE TABLE `sys_ticketpredefinedreplies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rname` varchar(200) DEFAULT NULL,
  `reply` text,
  `tags` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `attachments` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_ticketreplies
# ------------------------------------------------------------

CREATE TABLE `sys_ticketreplies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `account` varchar(200) DEFAULT NULL,
  `reply_type` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `message` text,
  `replied_by` varchar(200) DEFAULT NULL,
  `admin` varchar(100) DEFAULT NULL,
  `attachments` text,
  `client_read` varchar(100) DEFAULT NULL,
  `admin_read` varchar(100) DEFAULT NULL,
  `rating` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_tickets
# ------------------------------------------------------------

CREATE TABLE `sys_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(100) DEFAULT NULL,
  `did` int(10) DEFAULT NULL,
  `aid` int(10) DEFAULT NULL,
  `pid` int(10) DEFAULT NULL,
  `sid` int(10) DEFAULT NULL,
  `lid` int(10) DEFAULT NULL,
  `oid` int(10) DEFAULT NULL,
  `company_id` int(10) DEFAULT NULL,
  `dname` varchar(100) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `account` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `cc` varchar(200) DEFAULT NULL,
  `bcc` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject` text,
  `message` text,
  `status` varchar(100) DEFAULT NULL,
  `urgency` varchar(100) DEFAULT NULL,
  `admin` varchar(100) DEFAULT NULL,
  `attachments` text,
  `last_reply` varchar(100) DEFAULT NULL,
  `flag` int(1) DEFAULT NULL,
  `escalated` int(1) DEFAULT NULL,
  `replying` int(1) DEFAULT NULL,
  `is_spam` int(1) DEFAULT NULL,
  `rating` int(2) DEFAULT NULL,
  `client_read` varchar(100) DEFAULT NULL,
  `admin_read` varchar(100) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `ttype` varchar(100) DEFAULT NULL,
  `tstart` varchar(100) DEFAULT NULL,
  `tend` varchar(100) DEFAULT NULL,
  `ttotal` varchar(100) DEFAULT NULL,
  `todo` text,
  `tags` text,
  `notes` text,
  `c1` varchar(255) DEFAULT NULL,
  `c2` varchar(255) DEFAULT NULL,
  `c3` varchar(255) DEFAULT NULL,
  `c4` varchar(255) DEFAULT NULL,
  `c5` text,
  `end_user_id` int(10) unsigned DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_transactions
# ------------------------------------------------------------

CREATE TABLE `sys_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(200) NOT NULL,
  `account_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(200) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `sub_type` varchar(200) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `category` varchar(200) DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `payer` varchar(200) DEFAULT NULL,
  `payee` varchar(200) DEFAULT NULL,
  `payerid` int(11) NOT NULL DEFAULT '0',
  `payeeid` int(11) NOT NULL DEFAULT '0',
  `method` varchar(200) DEFAULT NULL,
  `ref` varchar(200) DEFAULT NULL,
  `status` enum('Cleared','Uncleared','Reconciled','Void') NOT NULL DEFAULT 'Cleared',
  `description` text,
  `tags` text,
  `tax` decimal(18,2) NOT NULL DEFAULT '0.00',
  `date` date DEFAULT NULL,
  `dr` decimal(18,2) NOT NULL DEFAULT '0.00',
  `cr` decimal(18,2) NOT NULL DEFAULT '0.00',
  `bal` decimal(18,2) NOT NULL DEFAULT '0.00',
  `iid` int(11) NOT NULL DEFAULT '0',
  `currency_iso_code` char(3) DEFAULT NULL,
  `currency` int(11) NOT NULL DEFAULT '0',
  `currency_symbol` varchar(10) DEFAULT NULL,
  `currency_prefix` varchar(10) DEFAULT NULL,
  `currency_suffix` varchar(10) DEFAULT NULL,
  `currency_rate` decimal(11,8) NOT NULL DEFAULT '1.0000',
  `base_amount` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `vid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL DEFAULT '0',
  `attachments` text,
  `source` varchar(200) DEFAULT NULL,
  `rid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `trash` int(1) NOT NULL DEFAULT '0',
  `flag` int(1) NOT NULL DEFAULT '0',
  `c1` text,
  `c2` text,
  `c3` text,
  `c4` text,
  `c5` text,
  `purchase_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_units
# ------------------------------------------------------------

CREATE TABLE `sys_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `reference` varchar(200) DEFAULT NULL,
  `conversion_factor` decimal(16,2) NOT NULL DEFAULT '0.00',
  `sorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sys_users
# ------------------------------------------------------------

CREATE TABLE `sys_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL DEFAULT '',
  `fullname` varchar(100) NOT NULL DEFAULT '',
  `phonenumber` varchar(20) DEFAULT NULL,
  `password` text,
  `user_type` varchar(50) NOT NULL DEFAULT 'Full Access',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `creationdate` datetime NOT NULL,
  `otp` enum('Yes','No') NOT NULL DEFAULT 'No',
  `pin_enabled` enum('Yes','No') NOT NULL DEFAULT 'No',
  `pin` mediumtext NOT NULL,
  `img` text NOT NULL,
  `api` enum('Yes','No') DEFAULT 'No',
  `pwresetkey` varchar(100) NOT NULL,
  `keyexpire` varchar(100) NOT NULL,
  `roleid` int(11) NOT NULL DEFAULT '0',
  `role` varchar(200) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `autologin` varchar(200) DEFAULT NULL,
  `at` varchar(200) DEFAULT NULL,
  `landing_page` varchar(200) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `sms_notify` int(1) DEFAULT NULL,
  `email_notify` int(1) DEFAULT NULL,
  `slack_notify` int(1) DEFAULT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `department_id` int(11) DEFAULT '0',
  `manager_id` int(11) DEFAULT '0',
  `pay_frequency` varchar(150) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `amount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `employee_id` varchar(150) DEFAULT NULL,
  `legal_name_title` varchar(150) DEFAULT NULL,
  `legal_name_first` varchar(150) DEFAULT NULL,
  `legal_name_mi` varchar(150) DEFAULT NULL,
  `legal_name_last` varchar(150) DEFAULT NULL,
  `banking_name` varchar(150) DEFAULT NULL,
  `ssn` varchar(150) DEFAULT NULL,
  `gender` varchar(150) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `marital_status` varchar(150) DEFAULT NULL,
  `ethnicity` varchar(150) DEFAULT NULL,
  `is_citizen` tinyint(1) NOT NULL DEFAULT '1',
  `has_i9_form` varchar(150) DEFAULT NULL,
  `work_authorization_expires` date DEFAULT NULL,
  `address_line_1` varchar(150) DEFAULT NULL,
  `address_line_2` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `state` varchar(150) DEFAULT NULL,
  `zip` varchar(150) DEFAULT NULL,
  `country` varchar(150) DEFAULT NULL,
  `work_phone` varchar(150) DEFAULT NULL,
  `work_mobile` varchar(150) DEFAULT NULL,
  `work_fax` varchar(150) DEFAULT NULL,
  `cc_email` varchar(150) DEFAULT NULL,
  `other` varchar(150) DEFAULT NULL,
  `emergency_contact_name_1` varchar(150) DEFAULT NULL,
  `emergency_contact_phone_1` varchar(150) DEFAULT NULL,
  `emergency_contact_relation_1` varchar(150) DEFAULT NULL,
  `emergency_contact_name_2` varchar(150) DEFAULT NULL,
  `emergency_contact_phone_2` varchar(150) DEFAULT NULL,
  `emergency_contact_relation_2` varchar(150) DEFAULT NULL,
  `last_day_worked` date DEFAULT NULL,
  `last_day_on_benefits` date DEFAULT NULL,
  `last_day_on_payroll` date DEFAULT NULL,
  `termination_type` varchar(150) DEFAULT NULL,
  `termination_reason` varchar(150) DEFAULT NULL,
  `is_recommended` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `facebook` varchar(150) DEFAULT NULL,
  `google` varchar(150) DEFAULT NULL,
  `linkedin` varchar(150) DEFAULT NULL,
  `skype` varchar(150) DEFAULT NULL,
  `twitter` varchar(150) DEFAULT NULL,
  `summary` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `sys_users` (`id`, `username`, `fullname`, `phonenumber`, `password`, `user_type`, `status`, `last_login`, `email`, `creationdate`, `otp`, `pin_enabled`, `pin`, `img`, `api`, `pwresetkey`, `keyexpire`, `roleid`, `role`, `last_activity`, `autologin`, `at`, `landing_page`, `language`, `notes`, `created_at`, `updated_at`, `sms_notify`, `email_notify`, `slack_notify`)
VALUES
	(1,'a.afzal@almutlak.com','Anees Afzal','1650','$2y$10$MpQAnMvMQ2Eu6yOKNyW2l.oriRR4Ndowynk3F0MYAiKgVBqRl/nRy','Admin','Active','2018-11-20 17:19:40','','2014-10-20 01:43:07','No','No','Y6AOTKNSQ5D7J4FU','','No','','0',0,NULL,NULL,NULL,NULL,NULL,'en',NULL,NULL,'2018-11-18 16:41:47',1,1,NULL);

CREATE TABLE `clx_projects` (
                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `created_by_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `proposal_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `project_manager_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `members` text COLLATE utf8mb4_unicode_ci,
                                `description` text COLLATE utf8mb4_unicode_ci,
                                `status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `priority` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `start_date` date DEFAULT NULL,
                                `due_date` date DEFAULT NULL,
                                `estimate_finish_date` date DEFAULT NULL,
                                `actual_finish_date` date DEFAULT NULL,
                                `brief` text COLLATE utf8mb4_unicode_ci,
                                `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `billing_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `rate` decimal(16,8) DEFAULT NULL,
                                `budget` decimal(16,8) DEFAULT NULL,
                                `logged_seconds` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                `total_expense` decimal(16,8) DEFAULT NULL,
                                `owner_id` int(10) UNSIGNED DEFAULT NULL,
                                `manager_id` int(10) UNSIGNED DEFAULT NULL,
                                `contact_can_view_task` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_create_task` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_edit_task` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_comment` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_view_time` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_upload_file` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_discuss` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_view_timesheet` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_view_activity_log` tinyint(1) NOT NULL DEFAULT '0',
                                `contact_can_view_members` tinyint(1) NOT NULL DEFAULT '0',
                                `tab_tasks` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_timesheet` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_milestones` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_files` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_discussions` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_gantt_view` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_tickets` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_invoices` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_proposals` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_members` tinyint(1) NOT NULL DEFAULT '1',
                                `tab_calendar` tinyint(1) NOT NULL DEFAULT '1',
                                `deleted_at` timestamp NULL DEFAULT NULL,
                                `created_at` timestamp NULL DEFAULT NULL,
                                `updated_at` timestamp NULL DEFAULT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lead_forms` (
                              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                              `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `form_data` text COLLATE utf8mb4_unicode_ci,
                              `lead_source_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `lead_status_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `notify_ids` text COLLATE utf8mb4_unicode_ci,
                              `captcha` tinyint(1) NOT NULL DEFAULT '0',
                              `submit_button_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `success_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
                              `allow_duplicate` tinyint(1) NOT NULL DEFAULT '1',
                              `create_task` tinyint(1) NOT NULL DEFAULT '0',
                              `webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `notification_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `created_at` timestamp NULL DEFAULT NULL,
                              `updated_at` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `widgets` (
                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                           `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                           `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                           `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                           `is_active` tinyint(1) NOT NULL DEFAULT '1',
                           `created_at` timestamp NULL DEFAULT NULL,
                           `updated_at` timestamp NULL DEFAULT NULL,
                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sys_purchases` (
                                 `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `userid` int(10) NOT NULL,
                                 `supplier_id` int(10) DEFAULT NULL,
                                 `supplier_name` varchar(200) DEFAULT NULL,
                                 `account` varchar(200) NOT NULL,
                                 `title` varchar(200) DEFAULT NULL,
                                 `code` varchar(100) DEFAULT NULL,
                                 `cn` varchar(100) NOT NULL DEFAULT '',
                                 `invoicenum` text NOT NULL,
                                 `date` date DEFAULT NULL,
                                 `duedate` date DEFAULT NULL,
                                 `datepaid` datetime DEFAULT NULL,
                                 `subtotal` decimal(18,2) NOT NULL,
                                 `discount_type` varchar(1) NOT NULL DEFAULT 'f',
                                 `discount_value` decimal(14,2) NOT NULL DEFAULT '0.00',
                                 `discount` decimal(14,2) NOT NULL DEFAULT '0.00',
                                 `credit` decimal(10,2) NOT NULL DEFAULT '0.00',
                                 `taxname` varchar(100) DEFAULT NULL,
                                 `tax` decimal(10,2) DEFAULT NULL,
                                 `tax2` decimal(10,2) DEFAULT NULL,
                                 `tax1_total` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                 `tax2_total` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                 `tax3_total` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                 `tax_total` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                 `total` decimal(18,2) NOT NULL DEFAULT '0.00',
                                 `taxrate` decimal(10,3) DEFAULT NULL,
                                 `taxrate2` decimal(10,3) DEFAULT NULL,
                                 `status` varchar(200) DEFAULT NULL,
                                 `paymentmethod` text NOT NULL,
                                 `notes` text NOT NULL,
                                 `vtoken` varchar(20) NOT NULL,
                                 `ptoken` varchar(20) NOT NULL,
                                 `r` varchar(100) NOT NULL DEFAULT '0',
                                 `nd` date DEFAULT NULL,
                                 `eid` int(10) NOT NULL DEFAULT '0',
                                 `ename` varchar(200) NOT NULL DEFAULT '',
                                 `vid` int(11) NOT NULL DEFAULT '0',
                                 `currency` int(11) NOT NULL DEFAULT '0',
                                 `currency_iso_code` char(3) DEFAULT NULL,
                                 `currency_symbol` varchar(10) DEFAULT NULL,
                                 `currency_prefix` varchar(10) DEFAULT NULL,
                                 `currency_suffix` varchar(10) DEFAULT NULL,
                                 `currency_rate` decimal(11,8) NOT NULL DEFAULT '1.0000',
                                 `recurring` tinyint(1) NOT NULL DEFAULT '0',
                                 `recurring_ends` date DEFAULT NULL,
                                 `last_recurring_date` date DEFAULT NULL,
                                 `source` varchar(200) DEFAULT NULL,
                                 `sale_agent` int(11) NOT NULL DEFAULT '0',
                                 `last_overdue_reminder` date DEFAULT NULL,
                                 `allowed_payment_methods` text,
                                 `billing_street` varchar(200) DEFAULT NULL,
                                 `billing_city` varchar(100) DEFAULT NULL,
                                 `billing_state` varchar(100) DEFAULT NULL,
                                 `billing_zip` varchar(50) DEFAULT NULL,
                                 `billing_country` varchar(100) DEFAULT NULL,
                                 `shipping_street` varchar(200) DEFAULT NULL,
                                 `shipping_city` varchar(100) DEFAULT NULL,
                                 `shipping_state` varchar(100) DEFAULT NULL,
                                 `shipping_zip` varchar(100) DEFAULT NULL,
                                 `shipping_country` varchar(100) DEFAULT NULL,
                                 `q_hide` tinyint(1) NOT NULL DEFAULT '0',
                                 `show_quantity_as` varchar(100) DEFAULT NULL,
                                 `pid` int(11) NOT NULL DEFAULT '0',
                                 `is_credit_invoice` int(1) NOT NULL DEFAULT '0',
                                 `aid` int(11) NOT NULL DEFAULT '0',
                                 `aname` varchar(200) DEFAULT NULL,
                                 `receipt_number` varchar(200) DEFAULT NULL,
                                 `stage` varchar(200) DEFAULT 'Pending',
                                 `subject` varchar(200) DEFAULT NULL,
                                 `created_at` timestamp NULL DEFAULT NULL,
                                 `updated_at` timestamp NULL DEFAULT NULL,
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sys_purchaseitems` (
                                     `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                     `invoiceid` int(10) NOT NULL DEFAULT '0',
                                     `userid` int(10) NOT NULL,
                                     `type` text NOT NULL,
                                     `relid` int(10) NOT NULL,
                                     `itemcode` varchar(100) NOT NULL,
                                     `tax_code` varchar(200) DEFAULT NULL,
                                     `description` text NOT NULL,
                                     `qty` varchar(20) NOT NULL DEFAULT '1',
                                     `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
                                     `taxed` int(1) NOT NULL,
                                     `tax_rate` decimal(16,3) DEFAULT NULL,
                                     `tax_name` varchar(200) DEFAULT NULL,
                                     `taxamount` decimal(10,2) NOT NULL DEFAULT '0.00',
                                     `tax1` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                     `tax2` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                     `tax3` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                     `discount_type` varchar(100) DEFAULT NULL,
                                     `discount_amount` decimal(16,4) NOT NULL DEFAULT '0.0000',
                                     `total` decimal(14,2) NOT NULL DEFAULT '0.00',
                                     `duedate` date DEFAULT NULL,
                                     `paymentmethod` text NOT NULL,
                                     `notes` text NOT NULL,
                                     `created_at` timestamp NULL DEFAULT NULL,
                                     `updated_at` timestamp NULL DEFAULT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bills` (
                         `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                         `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `from_account_id` int(10) UNSIGNED DEFAULT NULL,
                         `contact_id` int(10) UNSIGNED DEFAULT NULL,
                         `category_id` int(10) UNSIGNED DEFAULT NULL,
                         `start_date` date DEFAULT NULL,
                         `next_date` date NOT NULL,
                         `last_paid_date` date DEFAULT NULL,
                         `end_date` date DEFAULT NULL,
                         `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `net_amount` decimal(16,8) NOT NULL,
                         `recurring_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `remind_days_before` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                         `add_transaction_automatically` tinyint(1) NOT NULL DEFAULT '0',
                         `is_active` tinyint(1) NOT NULL DEFAULT '1',
                         `is_paid` tinyint(1) NOT NULL DEFAULT '0',
                         `is_skipped` tinyint(1) NOT NULL DEFAULT '0',
                         `created_at` timestamp NULL DEFAULT NULL,
                         `updated_at` timestamp NULL DEFAULT NULL,
                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipping_addresses` (
                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `contact_id` int(10) UNSIGNED NOT NULL,
                                      `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                      `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `is_default` tinyint(1) NOT NULL DEFAULT '0',
                                      `is_active` tinyint(1) NOT NULL DEFAULT '1',
                                      `created_at` timestamp NULL DEFAULT NULL,
                                      `updated_at` timestamp NULL DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `automation_tasks`
(
    `id`          bigint(20) UNSIGNED                     NOT NULL AUTO_INCREMENT,
    `admin_id`    int(10) UNSIGNED                        NOT NULL DEFAULT '0',
    `uuid`        varchar(36) COLLATE utf8mb4_unicode_ci  NULL     DEFAULT NULL,
    `name`        varchar(150) COLLATE utf8mb4_unicode_ci NULL     DEFAULT NULL,
    `action`        varchar(150) COLLATE utf8mb4_unicode_ci NULL     DEFAULT NULL,
    `payload` longtext COLLATE utf8mb4_unicode_ci         NULL,
    `completed`      tinyint(1)                              NOT NULL DEFAULT '0',
    `archived`    tinyint(1)                              NOT NULL DEFAULT '0',
    `created_at`  timestamp                               NULL     DEFAULT NULL,
    `updated_at`  timestamp                               NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `contracts` (
                             `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                             `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                             `owner_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                             `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                             `project_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                             `invoice_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                             `subscription_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                             `title` varchar(161) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `type` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `prefix` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `number` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `start_date` date DEFAULT NULL,
                             `expiration_date` date DEFAULT NULL,
                             `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `amount` decimal(8,2) DEFAULT NULL,
                             `description` text COLLATE utf8mb4_unicode_ci,
                             `party_one_first_name` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_one_last_name` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_one_email` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_one_sign` text COLLATE utf8mb4_unicode_ci,
                             `party_one_sign_ip_address` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_one_sign_date` date DEFAULT NULL,
                             `party_one_signed` tinyint(1) NOT NULL DEFAULT '0',
                             `party_two_first_name` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_two_last_name` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_two_email` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_two_sign` text COLLATE utf8mb4_unicode_ci,
                             `party_two_sign_ip_address` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `party_two_sign_date` date DEFAULT NULL,
                             `party_two_signed` tinyint(1) NOT NULL DEFAULT '0',
                             `status` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `archived` tinyint(1) NOT NULL DEFAULT '0',
                             `show_in_customer` tinyint(1) NOT NULL DEFAULT '0',
                             `created_at` timestamp NULL DEFAULT NULL,
                             `updated_at` timestamp NULL DEFAULT NULL,
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `subscriptions` (
                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                 `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                 `owner_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                 `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                 `plan_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                 `contract_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                 `invoice_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                 `title` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `type` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `term` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `start_date` date DEFAULT NULL,
                                 `end_date` date DEFAULT NULL,
                                 `next_renewal_date` date DEFAULT NULL,
                                 `amount` decimal(8,2) DEFAULT NULL,
                                 `description` text COLLATE utf8mb4_unicode_ci,
                                 `terms` text COLLATE utf8mb4_unicode_ci,
                                 `status` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `payment_gateway_api_name` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `payment_gateway_plan` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                 `archived` tinyint(1) NOT NULL DEFAULT '0',
                                 `paused` tinyint(1) NOT NULL DEFAULT '0',
                                 `cancelled` tinyint(1) NOT NULL DEFAULT '0',
                                 `terminated` tinyint(1) NOT NULL DEFAULT '0',
                                 `tax_included` tinyint(1) NOT NULL DEFAULT '0',
                                 `show_in_customer` tinyint(1) NOT NULL DEFAULT '0',
                                 `created_at` timestamp NULL DEFAULT NULL,
                                 `updated_at` timestamp NULL DEFAULT NULL,
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subscription_plans` (
                                      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                      `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                      `group_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                      `title` varchar(161) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `slug` varchar(161) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `type` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `term` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `price` decimal(8,2) DEFAULT NULL,
                                      `tax_1` decimal(8,2) DEFAULT NULL,
                                      `tax_2` decimal(8,2) DEFAULT NULL,
                                      `description` text COLLATE utf8mb4_unicode_ci,
                                      `features` text COLLATE utf8mb4_unicode_ci,
                                      `thank_you_message` text COLLATE utf8mb4_unicode_ci,
                                      `button_text` varchar(161) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `status` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `email_subject` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `email_body` text COLLATE utf8mb4_unicode_ci,
                                      `payment_gateway_api_name` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `payment_gateway_plan` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `stripe_pricing_id` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                      `archived` tinyint(1) NOT NULL DEFAULT '0',
                                      `tax_included` tinyint(1) NOT NULL DEFAULT '0',
                                      `show_in_customer` tinyint(1) NOT NULL DEFAULT '0',
                                      `created_at` timestamp NULL DEFAULT NULL,
                                      `updated_at` timestamp NULL DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
                            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                            `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                            `related_to` varchar(161) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                            `relation_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                            `description` text COLLATE utf8mb4_unicode_ci,
                            `show_in_customer` tinyint(1) NOT NULL DEFAULT '0',
                            `created_at` timestamp NULL DEFAULT NULL,
                            `updated_at` timestamp NULL DEFAULT NULL,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `short_urls` (
                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                              `short` char(6) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `original_url` varchar(161) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `access_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
                              `created_at` timestamp NULL DEFAULT NULL,
                              `updated_at` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `short_url_accesses` (
                                      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                      `url_id` int(10) UNSIGNED NOT NULL,
                                      `ip` int(10) UNSIGNED NOT NULL DEFAULT '0',
                                      `user_agent` varchar(161) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
                                      `country` char(2) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
                                      `created_at` timestamp NULL DEFAULT NULL,
                                      `updated_at` timestamp NULL DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


# Do not remove the comment, this is connected to the automation
# Insert Start
INSERT INTO `sys_permissions` (`pname`, `shortname`, `available`, `core`) VALUES
('Customers', 'customers', 0, 1),
('Companies', 'companies', 0, 1),
('Transactions', 'transactions', 0, 1),
('Sales', 'sales', 0, 1),
('Bank & Cash', 'bank_n_cash', 0, 1),
('Products & Services', 'products_n_services', 0, 1),
('Reports', 'reports', 0, 1),
('Utilities', 'utilities', 0, 1),
('Appearance', 'appearance', 0, 1),
('Plugins', 'plugins', 0, 1),
('Calendar', 'calendar', 0, 1),
('Leads', 'leads', 0, 1),
('Tasks', 'tasks', 0, 1),
('Contracts', 'contracts', 0, 1),
('Orders', 'orders', 0, 1),
('Settings', 'settings', 0, 1),
('Documents', 'documents', 0, 1),
('Purchase', 'purchase', 0, 1),
('Suppliers', 'suppliers', 0, 1),
('SMS', 'sms', 0, 1),
('Support', 'support', 0, 1),
('Knowledgebase', 'kb', 0, 1),
('Password Manager', 'password_manager', 0, 1),
('HRM', 'hr', 0, 1),
('Projects', 'projects', 0, 1),
('Accounting Dashboard', 'accounting_dashboard', 0, 1),
('Accounting GL Accounts', 'accounting_gl_accounts', 0, 1),
('Accounting Journal Entries', 'accounting_journal_entries', 0, 1),
('Accounting Accounts Receivable', 'accounting_ar', 0, 1),
('Accounting Accounts Payable', 'accounting_ap', 0, 1),
('Accounting Bank Reconciliation', 'accounting_bank_reconciliation', 0, 1),
('Accounting Financial Reports', 'accounting_reports', 0, 1),
('Accounting Tax & VAT', 'accounting_tax_vat', 0, 1),
('Accounting Settings', 'accounting_settings', 0, 1);

# Insert End


-- ============================================================
-- LIVE-SAFE ACCOUNTING SCHEMA PATCH
-- Generated: 2026-04-27
-- Included in installer for fresh system provisioning
-- ============================================================

-- 1) Missing invoice columns (idempotent)
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_invoices' AND COLUMN_NAME = 'zatca_hash'
);
SET @sql_stmt := IF(
  @col_exists = 0,
  'ALTER TABLE `sys_invoices` ADD COLUMN `zatca_hash` VARCHAR(512) NULL DEFAULT NULL COMMENT "Invoice SHA256 hash"',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_invoices' AND COLUMN_NAME = 'zatca_ca_signature'
);
SET @sql_stmt := IF(
  @col_exists = 0,
  'ALTER TABLE `sys_invoices` ADD COLUMN `zatca_ca_signature` LONGTEXT NULL DEFAULT NULL COMMENT "CA certificate signature"',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_invoices' AND COLUMN_NAME = 'journal_entry_id'
);
SET @sql_stmt := IF(
  @col_exists = 0,
  'ALTER TABLE `sys_invoices` ADD COLUMN `journal_entry_id` BIGINT NULL COMMENT "GL Journal Entry for AR" AFTER `id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Duplicate invoice prevention index (idempotent, non-destructive)
SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_invoices' AND INDEX_NAME = 'unique_invoicenum_cn_type'
);
SET @sql_stmt := IF(
  @idx_exists = 0,
  'ALTER TABLE `sys_invoices` ADD UNIQUE INDEX `unique_invoicenum_cn_type` (`invoicenum`(100), `cn`(100), `type`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Core accounting tables (safe create only)
CREATE TABLE IF NOT EXISTS `sys_gl_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('asset','liability','equity','revenue','expense') NOT NULL,
  `parent_id` INT NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `normal_balance` ENUM('debit','credit') NOT NULL DEFAULT 'debit',
  `allow_manual_posting` TINYINT(1) DEFAULT 0,
  `is_control_account` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_code` (`code`),
  INDEX `idx_type` (`type`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_journal_entries` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `entry_date` DATE NOT NULL,
  `reference` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `source_module` VARCHAR(50) NOT NULL,
  `source_id` BIGINT NULL,
  `company_id` INT NULL,
  `entered_by_user_id` INT NOT NULL,
  `posted_by_user_id` INT NULL,
  `post_status` ENUM('draft','posted','reversed') NOT NULL DEFAULT 'draft',
  `post_date` DATETIME NULL,
  `reversal_of_je_id` BIGINT NULL,
  `reversing_je_id` BIGINT NULL,
  `period_id` INT NULL,
  `memo` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_entry_date` (`entry_date`),
  INDEX `idx_post_status` (`post_status`),
  INDEX `idx_source_module` (`source_module`),
  INDEX `idx_reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_journal_items` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `journal_entry_id` BIGINT NOT NULL,
  `account_id` INT NOT NULL,
  `debit` DECIMAL(15,2) DEFAULT 0,
  `credit` DECIMAL(15,2) DEFAULT 0,
  `description` TEXT NULL,
  `line_no` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_journal_entry_id` (`journal_entry_id`),
  INDEX `idx_account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_gl_account_balances` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL UNIQUE,
  `currency_id` INT NULL,
  `debit_balance` DECIMAL(15,2) DEFAULT 0,
  `credit_balance` DECIMAL(15,2) DEFAULT 0,
  `net_balance` DECIMAL(15,2) DEFAULT 0,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_account_currency` (`account_id`, `currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_bank_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `account_number` VARCHAR(100) NULL,
  `bank_name` VARCHAR(255) NULL,
  `currency_id` INT NULL,
  `gl_cash_account_id` INT NOT NULL,
  `balance` DECIMAL(15,2) DEFAULT 0,
  `reconciled_balance` DECIMAL(15,2) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_bank_reconciliations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bank_account_id` INT NOT NULL,
  `statement_date` DATE NOT NULL,
  `opening_balance` DECIMAL(15,2),
  `closing_balance` DECIMAL(15,2),
  `book_balance` DECIMAL(15,2),
  `status` ENUM('draft','reconciled','locked') DEFAULT 'draft',
  `reconciled_by_user_id` INT NULL,
  `reconciled_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_bank_account_id` (`bank_account_id`),
  INDEX `idx_statement_date` (`statement_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_bank_reconciliation_matches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reconciliation_id` INT NOT NULL,
  `journal_entry_id` BIGINT NOT NULL,
  `statement_line_text` VARCHAR(255) NULL,
  `matched_amount` DECIMAL(15,2),
  `match_type` ENUM('cleared','pending') DEFAULT 'cleared',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_reconciliation_id` (`reconciliation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_tax_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE,
  `name` VARCHAR(100),
  `rate` DECIMAL(5,3),
  `tax_type` ENUM('sales','purchase','both') DEFAULT 'both',
  `gl_payable_account_id` INT NULL,
  `gl_recoverable_account_id` INT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_zatca_compliant` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_ar_ledger` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` BIGINT NOT NULL,
  `journal_entry_id` BIGINT NOT NULL,
  `invoice_id` BIGINT NULL,
  `amount` DECIMAL(15,2),
  `transaction_type` ENUM('invoice','payment','adjustment') DEFAULT 'invoice',
  `posting_date` DATE NOT NULL,
  `due_date` DATE NULL,
  `is_allocated` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_posting_date` (`posting_date`),
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_ap_ledger` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` BIGINT NOT NULL,
  `journal_entry_id` BIGINT NOT NULL,
  `bill_id` BIGINT NULL,
  `amount` DECIMAL(15,2),
  `transaction_type` ENUM('bill','payment','adjustment') DEFAULT 'bill',
  `posting_date` DATE NOT NULL,
  `due_date` DATE NULL,
  `is_allocated` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_vendor_id` (`vendor_id`),
  INDEX `idx_posting_date` (`posting_date`),
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_audit_logs` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `table_name` VARCHAR(50) NOT NULL,
  `record_id` BIGINT NULL,
  `old_value` LONGTEXT NULL,
  `new_value` LONGTEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_action` (`user_id`, `action`),
  INDEX `idx_table_record` (`table_name`, `record_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_accounting_periods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `period_name` VARCHAR(50) NOT NULL UNIQUE,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('open','locked','closed') DEFAULT 'open',
  `locked_by_user_id` INT NULL,
  `locked_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Backfill GL foreign key column references where possible
SET @je_table_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_journal_entries'
);
SET @invoice_col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_invoices' AND COLUMN_NAME = 'journal_entry_id'
);
SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_invoices' AND COLUMN_NAME = 'journal_entry_id' AND REFERENCED_TABLE_NAME = 'sys_journal_entries'
);
SET @sql_stmt := IF(
  @je_table_exists = 1 AND @invoice_col_exists = 1 AND @fk_exists = 0,
  'ALTER TABLE `sys_invoices` ADD CONSTRAINT `fk_sys_invoices_journal_entry_id` FOREIGN KEY (`journal_entry_id`) REFERENCES `sys_journal_entries`(`id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5) Seed core chart of accounts (idempotent)
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '1000','Cash - General','asset','debit',0,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='1000');
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '1100','Accounts Receivable','asset','debit',1,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='1100');
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '1250','VAT Recoverable (Input Tax)','asset','debit',0,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='1250');
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '2000','Accounts Payable','liability','credit',1,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='2000');
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '2100','VAT Payable (Output Tax)','liability','credit',0,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='2100');
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '4000','Sales Revenue - Domestic','revenue','credit',0,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='4000');
INSERT INTO `sys_gl_accounts` (`code`,`name`,`type`,`normal_balance`,`is_control_account`,`is_active`)
SELECT '5000','Cost of Goods Sold','expense','debit',0,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_gl_accounts` WHERE `code`='5000');

-- 6) Seed VAT tax codes (idempotent)
SET @vat_payable := (SELECT id FROM `sys_gl_accounts` WHERE code='2100' LIMIT 1);
SET @vat_recoverable := (SELECT id FROM `sys_gl_accounts` WHERE code='1250' LIMIT 1);

INSERT INTO `sys_tax_codes` (`code`,`name`,`rate`,`tax_type`,`gl_payable_account_id`,`gl_recoverable_account_id`,`is_active`,`is_zatca_compliant`)
SELECT 'VAT-15','Standard VAT (15%)',15.000,'both',@vat_payable,@vat_recoverable,1,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_tax_codes` WHERE `code`='VAT-15');

INSERT INTO `sys_tax_codes` (`code`,`name`,`rate`,`tax_type`,`gl_payable_account_id`,`gl_recoverable_account_id`,`is_active`,`is_zatca_compliant`)
SELECT 'VAT-0','Zero-Rated (Exports)',0.000,'sales',@vat_payable,NULL,1,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_tax_codes` WHERE `code`='VAT-0');

INSERT INTO `sys_tax_codes` (`code`,`name`,`rate`,`tax_type`,`gl_payable_account_id`,`gl_recoverable_account_id`,`is_active`,`is_zatca_compliant`)
SELECT 'VAT-EXEMPT','Tax Exempt',0.000,'both',NULL,NULL,1,1
WHERE NOT EXISTS (SELECT 1 FROM `sys_tax_codes` WHERE `code`='VAT-EXEMPT');

-- 7) Seed current accounting period if absent
SET @period_name := DATE_FORMAT(CURRENT_DATE(), '%Y-%m');
SET @period_start := DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01');
SET @period_end := LAST_DAY(CURRENT_DATE());
INSERT INTO `sys_accounting_periods` (`period_name`,`start_date`,`end_date`,`status`)
SELECT @period_name, @period_start, @period_end, 'open'
WHERE NOT EXISTS (
  SELECT 1 FROM `sys_accounting_periods`
  WHERE `period_name` = @period_name
);

-- 8) Contact-level AR/AP mapping on crm_accounts (idempotent)
SET @crm_accounts_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_accounts'
);

SET @crm_ar_col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_accounts' AND COLUMN_NAME = 'ar_account_id'
);
SET @sql_stmt := IF(
  @crm_accounts_exists = 1 AND @crm_ar_col_exists = 0,
  'ALTER TABLE `crm_accounts` ADD COLUMN `ar_account_id` INT NULL COMMENT "AR control account mapping" AFTER `type`',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @crm_ap_col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_accounts' AND COLUMN_NAME = 'ap_account_id'
);
SET @sql_stmt := IF(
  @crm_accounts_exists = 1 AND @crm_ap_col_exists = 0,
  'ALTER TABLE `crm_accounts` ADD COLUMN `ap_account_id` INT NULL COMMENT "AP control account mapping" AFTER `ar_account_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @crm_ar_idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_accounts' AND INDEX_NAME = 'idx_crm_accounts_ar_account_id'
);
SET @sql_stmt := IF(
  @crm_accounts_exists = 1 AND @crm_ar_idx_exists = 0,
  'ALTER TABLE `crm_accounts` ADD INDEX `idx_crm_accounts_ar_account_id` (`ar_account_id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @crm_ap_idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_accounts' AND INDEX_NAME = 'idx_crm_accounts_ap_account_id'
);
SET @sql_stmt := IF(
  @crm_accounts_exists = 1 AND @crm_ap_idx_exists = 0,
  'ALTER TABLE `crm_accounts` ADD INDEX `idx_crm_accounts_ap_account_id` (`ap_account_id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @ar_control_account_id := (SELECT id FROM `sys_gl_accounts` WHERE code = '1100' LIMIT 1);
SET @ap_control_account_id := (SELECT id FROM `sys_gl_accounts` WHERE code = '2000' LIMIT 1);

UPDATE `crm_accounts`
SET `ar_account_id` = @ar_control_account_id
WHERE @crm_accounts_exists = 1
  AND @ar_control_account_id IS NOT NULL
  AND `type` = 'Customer'
  AND (`ar_account_id` IS NULL OR `ar_account_id` = 0);

UPDATE `crm_accounts`
SET `ap_account_id` = @ap_control_account_id
WHERE @crm_accounts_exists = 1
  AND @ap_control_account_id IS NOT NULL
  AND `type` = 'Vendor'
  AND (`ap_account_id` IS NULL OR `ap_account_id` = 0);

-- ============================================================
-- End of live-safe accounting patch
-- ============================================================


-- ============================================================
-- MIRROR ENTRY (2026-04-28): Contact buyer_type + Company ZATCA fields
-- ============================================================

-- crm_accounts.buyer_type (needed for ZATCA buyer identification)
SET @buyer_type_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_accounts' AND COLUMN_NAME = 'buyer_type'
);
SET @sql_stmt := IF(@buyer_type_exists = 0,
    'ALTER TABLE `crm_accounts` ADD COLUMN `buyer_type` VARCHAR(20) NOT NULL DEFAULT \'\'',
    'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sys_companies.vat_number
SET @co_vat_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_companies' AND COLUMN_NAME = 'vat_number'
);
SET @sql_stmt := IF(@co_vat_exists = 0,
    'ALTER TABLE `sys_companies` ADD COLUMN `vat_number` VARCHAR(20) NOT NULL DEFAULT \'\'',
    'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sys_companies.crn_number
SET @co_crn_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_companies' AND COLUMN_NAME = 'crn_number'
);
SET @sql_stmt := IF(@co_crn_exists = 0,
    'ALTER TABLE `sys_companies` ADD COLUMN `crn_number` VARCHAR(20) NOT NULL DEFAULT \'\'',
    'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sys_companies.building_number
SET @co_bldg_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_companies' AND COLUMN_NAME = 'building_number'
);
SET @sql_stmt := IF(@co_bldg_exists = 0,
    'ALTER TABLE `sys_companies` ADD COLUMN `building_number` VARCHAR(20) NOT NULL DEFAULT \'\'',
    'SELECT 1'
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

