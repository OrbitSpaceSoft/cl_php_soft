<?php
namespace OrbitSpaceSoft\helpers;

class FileHelper
{
    public static function getHttpFiles()
    {
        $form = [];
        if( isset($_FILES) && is_array($_FILES) )
        {
            foreach ($_FILES as $key => $file)
            {
                if( is_array($file['size']) )
                {
                    foreach ( $file as $_k => $_vv )
                    {
                        foreach ($_vv as $_kk => $_v)
                        {
                            $_kk = 'n_'.$_kk;
                            if(($_k === 'size' && $_v === 0)
                                || ($_k === 'error' && $_v !== 0  )
                                || empty($_v)
                            ) continue;


                            if( !isset($form[$key]) )
                                $form[$key] = [];

                            if( !isset($form[$key][$_kk]) )
                                $form[$key][$_kk] = [];

                            if( $_k === 'tmp_name')
                            {
                                if( !empty($_v) )
                                    $form[$key][$_kk]['doc_code'] = base64_encode(file_get_contents($_v));

                                @unlink($_v);
                            }
                            else $form[$key][$_kk][$_k] = $_v;
                        }
                    }


                } else {
                    if ($file['size'] === 0 || $file['error'] !== 0) {
                        unset($form[$key]);
                        continue;
                    }

                    $form[$key] = [
                        'size' => $file['size'],
                        'name' => $file['name'],
                        'type' => $file['type'],
                        'doc_code' => base64_encode(file_get_contents($file['tmp_name'])),
                    ];

                    @unlink($file['tmp_name']);
                }
            }
        }

        return $form;
    }
}