<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 30/11/16
 * Time: 18:03
 */

namespace Mindy\Bundle\MindyBundle\Controller;

use Mindy\Bundle\MindyBundle\Components\UploadHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function createDirectoryAction(Request $request)
    {
        $path = $request->query->get('path', '/');
        $directoryName = $request->query->get('directory');

        if (empty($directoryName)) {
            return $this->json([
                'status' => false,
                'message' => $this->get('translator')->trans('file.directory.missing_name_error')
            ]);
        } else if (strpos($directoryName, '/') !== false) {
            return $this->json([
                'status' => false,
                'message' => $this->get('translator')->trans('file.directory.incorrect_name_error')
            ]);
        } else {
            $storage = $this->get('storage');
            $fs = $storage->getFilesystem();
            $dirPath = implode('/', [$path, $directoryName]);

            if ($fs->has($dirPath)) {
                return $this->json([
                    'status' => false,
                    'message' => $this->get('translator')->trans('file.directory.exist_error')
                ]);
            } else {
                if ($fs->createDir($dirPath)) {
                    return $this->json([
                        'status' => true,
                        'message' => $this->get('translator')->trans('file.directory.create_success')
                    ]);
                } else {
                    return $this->json([
                        'status' => true,
                        'message' => $this->get('translator')->trans('file.directory.create_error')
                    ]);
                }
            }
        }
    }

    public function listAction(Request $request)
    {
        $path = urldecode($request->query->get('path', '/'));

        $fs = $this->get('storage')->getFilesystem();

        $objects = [];
        foreach ($fs->listContents($path) as $object) {
            $objects[] = [
                'path' => '/' . $object['path'],
                'name' => basename($object['path']),
                'date' => date(DATE_W3C, $object['timestamp']),
                'is_dir' => $object['type'] === 'dir',
                'size' => $object['size'] ?? 0,
                'url' => $object['path']
            ];
        }

        $breadcrumbs = [
            [
                'url' => $this->generateUrl('file_list'),
                'name' => $this->get('translator')->trans('admin.file.name')
            ]
        ];
        $prev = [];
        foreach (array_filter(explode('/', $path)) as $part) {
            $prev[] = $part;

            $query = ['path' => '/' . implode('/', $prev)];
            $url = $this->generateUrl('file_list', $query);
            $breadcrumbs[] = ['url' => $url, 'name' => $part];
        }

        return $this->render('file/list.html', [
            'breadcrumbs' => $breadcrumbs,
            'objects' => $objects
        ]);
    }

    public function deleteAction(Request $request)
    {
        $path = $request->query->get('path', '/');
        $storage = $this->container->get('storage');
        $fs = $storage->getFilesystem();
        if ($fs->has($path)) {
            $meta = $fs->getMetadata($path);
            if ($meta['type'] === 'file') {
                $fs->delete($path);
            } else {
                $fs->deleteDir($path);
            }

            return $this->json(['status' => true]);
        } else {
            return $this->json(['status' => false, 'error' => 'Path not found']);
        }
    }

    public function uploadAction(Request $request)
    {
        $media = $this->getParameter('storage.media_dir');
        $path = $request->query->get('path', '/');

        $handler = new UploadHandler([
            'image_versions' => [],
            'upload_dir' => $media . DIRECTORY_SEPARATOR . trim($path, '/') . DIRECTORY_SEPARATOR
        ]);

        return new Response('');
    }
}