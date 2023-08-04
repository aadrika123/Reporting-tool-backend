<?php
   
   namespace app\ReportFieldType\Facades;
   use Illuminate\Support\Facades\Facade;
   
   class FieldType extends Facade {
      protected static function getFacadeAccessor() { return 'FieldType'; }
   }