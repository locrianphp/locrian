<?php

    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 10.09.2017
     * Time: 15:34
     */

    namespace Locrian\Tests;

    use Locrian\Collections\ArrayList;
    use Locrian\Core\Http\UploadedFile;
    use Locrian\Core\Http\UploadManager;
    use PHPUnit_Framework_TestCase;

    class UploadManagerTest extends PHPUnit_Framework_TestCase{

        private $filePaths = [ "tests/tmp/file1", "tests/tmp/file2", "tests/tmp/file3", "tests/tmp/file4" ];

        private $movedFilePaths = [ "tests/target/file1", "tests/target/file2", "tests/target/file3", "tests/target/file4" ];

        /**
         * @var \Locrian\Core\Http\UploadManager
         */
        private  $uploadManager;

        protected function setUp(){
            $files = new ArrayList();
            for( $i = 0; $i < count($this->filePaths); $i++ ){
                $file = new UploadedFile();
                $name = explode("/", $this->filePaths[$i]);
                $file->setName($name[count($name) - 1]);
                $file->setTmpName($this->filePaths[$i]);
                $file->setType("image/png");
                $file->setError(0);
                $file->setSize(1256);
                $files->add($file);
            }
            $this->uploadManager = new UploadManager($files);
        }

        public function testBasics(){
            self::assertTrue($this->uploadManager->isAllUploaded());
            $this->uploadManager->getFiles()->first()->setError(4);
            self::assertFalse($this->uploadManager->isAllUploaded());
            self::assertEquals(UploadManager::ERR_NO_FILE, $this->uploadManager->getFiles()->first()->getError());
            self::assertEquals([4], $this->uploadManager->getUploadErrors());
        }

        public function testValidationSuccess(){
            $this->uploadManager->setAllowedMaxFileSize(1300);
            $this->uploadManager->setAllowedMinFileSize(1000);
            $this->uploadManager->setAllowedMimeTypes(["image/png"]);
            $this->uploadManager->validate();
            self::assertFalse($this->uploadManager->hasValidationErrors());
            $this->uploadManager->clearValidationErrors();
        }

        public function testValidationFail(){
            $this->uploadManager->setAllowedMaxFileSize(1300);
            $this->uploadManager->setAllowedMinFileSize(1000);
            $this->uploadManager->setAllowedMimeTypes(["image/png"]);
            $file = $this->uploadManager->getFiles()->first();
            $file->setSize(1400);
            $file->setType("image/jpeg");
            $file = $this->uploadManager->getFiles()->last();
            $file->setSize(900);
            $this->uploadManager->validate();
            self::assertTrue($this->uploadManager->hasValidationErrors());
            $errorFiles = $this->uploadManager->getFailedValidationUploads();
            $errors1 = $errorFiles[0]->getErrorMessages();
            $errors2 = $errorFiles[1]->getErrorMessages();
            self::assertTrue(isset($errors1[UploadManager::MIME_ERROR]) && isset($errors1[UploadManager::MAX_SIZE_ERROR]) && count($errors1) === 2);
            self::assertTrue(isset($errors2[UploadManager::MIN_SIZE_ERROR]) && count($errors2) === 1);
            $this->uploadManager->clearValidationErrors();
        }

        private function createFiles(){
            mkdir("tests/tmp");
            mkdir("tests/target");
            for( $i = 0; $i < count($this->filePaths); $i++ ){
                file_put_contents($this->filePaths[$i], "data");
            }
        }

        public function testMoveAll(){
            $this->createFiles();
            $this->uploadManager->moveAll("tests/target");
            for( $i = 0; $i < count($this->filePaths); $i++ ){
                self::assertFalse(file_exists($this->filePaths[$i]));
                self::assertTrue(file_exists($this->movedFilePaths[$i]));
            }
            $this->deleteFiles();
        }

        public function testMoveAllWithName(){
            $this->createFiles();
            $this->uploadManager->moveAllWithName("tests/target", function($currentName){
                return $currentName . ".ext"; // Add extension
            });
            for( $i = 0; $i < count($this->filePaths); $i++ ){
                self::assertFalse(file_exists($this->filePaths[$i]));
                self::assertTrue(file_exists($this->movedFilePaths[$i] . ".ext"));
            }
            $this->deleteFiles();
        }

        private function deleteFiles(){
            for( $i = 0; $i < count($this->filePaths); $i++ ){
                if( file_exists($this->filePaths[$i]) ){
                    unlink($this->filePaths[$i]);
                }
            }
            for( $i = 0; $i < count($this->movedFilePaths); $i++ ){
                if( file_exists($this->movedFilePaths[$i]) ){
                    unlink($this->movedFilePaths[$i]);
                }
                if( file_exists($this->movedFilePaths[$i] . ".ext") ){
                    unlink($this->movedFilePaths[$i] . ".ext");
                }
            }
            rmdir("tests/tmp");
            rmdir("tests/target");
        }

    }