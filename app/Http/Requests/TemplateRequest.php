<?php

namespace App\Http\Requests;

use App\Bll\SaveTemplateBll;
use App\Models\VtSearchGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\RequiredIf;

class TemplateRequest extends FormRequest
{
    private $_mVtSearchGroup;
    private $_saveTemplateBll;
    public function __construct()
    {
        $this->_mVtSearchGroup = new VtSearchGroup();
        $this->_saveTemplateBll = new SaveTemplateBll;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $searchGroup = $this->_mVtSearchGroup::find($this->template['searchGroupId']);
        if (collect($searchGroup)->isEmpty()) {
            throw new HttpResponseException(
                response()->json(
                    [
                        'status' => false,
                        'message' => 'Search Group not Available',
                        'data' => []
                    ]
                )
            );
        }

        $this->merge(['isPdfReport' => $searchGroup->is_report]);
        $this->_saveTemplateBll->_isPdfReport = $searchGroup->is_report;

        $validation = [
            "template" => "required|array",                         // Template Validation
            "template.searchGroupId" => "required|integer",
            "template.templateCode" => "required|string",
            "template.templateName" => "required|string",
            "template.paperSizeEnum" => "nullable|string",
            "template.detailLayout" => "nullable|string",
            "template.detailLayout" => new RequiredIf($searchGroup->is_report == true),
            "template.headerHeight" => "nullable|integer",
            "template.headerHeight" => new RequiredIf($searchGroup->is_report == true),
            "template.headerHeightPage2" => "nullable|integer",
            "template.headerHeightPage2" => new RequiredIf($searchGroup->is_report == true),
            "template.footerHeight" => "nullable|integer",
            "template.footerHeight" => new RequiredIf($searchGroup->is_report == true),
            "template.detailLineSpacing" => "nullable|integer",
            "template.layoutSql" => "nullable|string",
            "template.detailSql" => "required|string",
            "template.footerSql" => "nullable|string",
            "template.isDefault" => "nullable|bool",
            "template.isDefault" => new RequiredIf($searchGroup->is_report == true),
            "template.isLandscape" => "nullable|bool",
            "template.isLandscape" => new RequiredIf($searchGroup->is_report == true),
            "template.isGlobalHeader" => "nullable|bool",
            "template.isRenderGlobalHeader" => "nullable|bool",
            "template.isPageLayoutInPager2" => "nullable|bool",
            "template.groupbyExpression" => "nullable|string",
            "template.isShowGridLine" => "nullable|bool",
            "template.headerDistance" => "nullable|integer",
            "template.screenDisplayString" => "nullable|string",
            "template.parentId" => "nullable|integer",
            "template.labelRowCount" => "nullable|integer",
            "template.labelColumnCount" => "nullable|integer",
            "template.isDetailWordwrap" => "nullable|bool",
            "template.isCompactFooter" => "nullable|bool",
            "template.moduleId" => "nullable|integer",
        ];

        if ($this->_saveTemplateBll->_isPdfReport) {                // Validations for Pdf Reports
            $validation = array_merge($validation, [
                "layouts" => 'required|array',                      // Layout Validation
                'layouts.*.fieldType' => 'required',
                'layouts.*.caption' => 'required',
                'layouts.*.fieldName' => 'required',
                'layouts.*.pageNo' => 'required',
                'layouts.*.x' => 'required',
                'layouts.*.y' => 'required',
                'layouts.*.width' => 'required',
                'layouts.*.height' => 'required',
                'layouts.*.fontName' => 'required',
                'layouts.*.fontSize' => 'required',
                'layouts.*.isUnderline' => 'required|bool',
                'layouts.*.isBold' => 'required|bool',
                'layouts.*.isItalic' => 'required|bool',
                'layouts.*.isVisible' => 'required|bool',
                'layouts.*.alignment' => 'required',
                'layouts.*.color' => 'required',
                'layouts.*.file' =>  'mimes:jpeg,jpg,png,gif|nullable|max:10000', // max 10000kb,


                'details' => 'required|array',                      // Details Validation
                'details.*.x' => 'required|numeric',
                'details.*.y' => 'required|numeric',
                'details.*.fieldType' => 'required|string',
                'details.*.fieldName' => 'required|string',
                'details.*.fontName' => 'required|string',
                'details.*.fontSize' => 'required|string',
                'details.*.width' => 'required|integer',
                'details.*.isUnderline' => 'required|bool',
                'details.*.isBold' => 'required|bool',
                'details.*.isItalic' => 'required|bool',
                'details.*.isVisible' => 'required|bool',
                'details.*.isBoxed' => 'required|bool',
                'details.*.alignment' => 'required|string',
                'details.*.color' => 'required|string',

                'footer' => 'required|array',
                'footer.*.serialNo' => 'required|integer',
                'footer.*.fieldType' => 'required',
                'footer.*.caption' => 'required',
                'footer.*.fieldName' => 'required|string',
                // 'footer.resource' => 'required|string',
                'footer.*.x' => 'required|numeric',
                'footer.*.y' => 'required|numeric',
                'footer.*.width' => 'required|numeric',
                'footer.*.height' => 'required|numeric',
                'footer.*.fontname' => 'required',
                'footer.*.size' => 'required',
                'footer.*.isUnderline' => 'required|bool',
                'footer.*.isBold' => 'required|bool',
                'footer.*.isItalic' => 'required|bool',
                'footer.*.isVisible' => 'required|bool',
                'footer.*.isBoxed' => 'required|bool',
                'footer.*.alignment' => 'required|string',
                'footer.*.color' => 'required|string',
            ]);

            if (isset($this->template['id'])) {
                $validation = array_merge($validation,  [
                    'layouts.*.id' => 'nullable|integer',
                    'details.*.id' => 'nullable|integer',
                    'footer.*.id' => 'nullable|integer'
                ]);
            }
        }

        if ($this->_saveTemplateBll->_isPdfReport == false) {       // Validation for Search Report Formats
            $validation = array_merge($validation, [
                "parameters" => "required|array",
                "parameters.*.serial" => "required|integer",
                "parameters.*.controlName" => "required|string",
                "parameters.*.displayString" => "required|string",
                "parameters.*.controlType" => "required|string|In:Date,Number,Text,Combo",
                "parameters.*.linkName" => "nullable|string",
                "parameters.*.sourceSql" => "nullable|string",
                "parameters.*.boundColumn" => "nullable|string",
                "parameters.*.displayColumn" => "nullable|string",
                "parameters.*.dependencyControlCode" => "nullable|string",
            ]);

            if (isset($this->template['id']))
                $validation = array_merge($validation, ['parameters.*.id' => 'nullable|integer']);
        }

        return $validation;
    }

    // Validation Error Message
    protected function failedValidation(ValidationValidator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'status' => false,
                    'message' => 'The given data was invalid',
                    'errors' => $validator->errors()
                ],
                422
            )
        );
    }
}
