<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class ApiController extends Controller
{
    public function loadTemplate()
    {
        return response()->file(storage_path('app/template/template_prestataire2.xlsx'));
    }

    public function csvupload(Request $request)
    {
        $file = $request->file('csv');

        // Check if file exists and delete it
        $tmp_file = storage_path('app/tmp.csv');
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }

        // Move uploaded file to storage directory
        $file->move(storage_path('app'), 'tmp.csv');

        return response()->json(['message' => 'CSV uploaded successfully!']);
    }


    public function csvToJson(Request $request)
    {
        $path = storage_path('app/tmp.csv');
        $file = fopen($path, 'r');
        $header = fgetcsv($file); // reads the header row
        $formattedHeader = array_map(function ($value) {
            $key = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(str_replace(' ', '_', $value)));
            return [
                'text' => $value,
                'sortable' => true,
                'key' => $key,
                'value' => $key,
            ];
        }, $header);
        $records = [];
        while (($row = fgetcsv($file)) !== false) {
            $record = [];
            foreach ($formattedHeader as $key => $value) {
                $record[$value['key']] = $row[$key];
            }
            $records[] = $record; // adds the record to the list of records
        }
        fclose($file);

        $json = (['headers' => $formattedHeader, 'items' => $records]);
        return response()->json($json);
    }


    public function getTemplateKeys()
    {
        $path = resource_path('template.json');
        $contents = file_get_contents($path);
        $data = json_decode($contents, true);
        return response()->json($data);
    }

    public function saveConfig(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'mappings' => 'required|array',
        ]);

        $mappings = $validatedData['mappings'];
        $mappingName = $validatedData['name'];

        $mappingFilePath = "mappings/{$mappingName}.json";
        $mappingJson = json_encode($mappings, JSON_PRETTY_PRINT);

        Storage::put($mappingFilePath, $mappingJson);

        return response()->json(['success' => true]);
    }

    public function getMappings()
    {
        $files = Storage::files('mappings');
        $mappings = array_map(function ($file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $contents = Storage::get($file);
            $data = json_decode($contents);
            return (object)[
                'filename' => $filename,
                'mappings' => $data,
            ];
        }, $files);
        return $mappings;
    }


}
