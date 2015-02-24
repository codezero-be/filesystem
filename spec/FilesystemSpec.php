<?php namespace spec\CodeZero\Filesystem;

use org\bovigo\vfs\vfsStream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FilesystemSpec extends ObjectBehavior
{
    private $baseDirectory;
    private $testFile;
    private $testDirectory;
    private $testFileData;
    private $testSubFile;
    private $testSubFileData;
    private $testSubFile2;
    private $testSubFileData2;
    private $directoryStructure;

    public function let()
    {
        $this->baseDirectory = 'baseDirectory';
        $this->testFile = 'test.txt';
        $this->testFileData = 'test';
        $this->testDirectory = 'subDirectory';
        $this->testSubFile = 'subFile.txt';
        $this->testSubFileData = 'subFile';
        $this->testSubFile2 = 'subFile2.txt';
        $this->testSubFileData2 = 'subFile2';

        $this->directoryStructure = [
            $this->testFile => $this->testFileData,
            $this->testDirectory => [
                $this->testSubFile => $this->testSubFileData,
                $this->testSubFile2 => $this->testSubFileData2
            ]
        ];

        clearstatcache();
        vfsStream::setup($this->baseDirectory, null, $this->directoryStructure);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('CodeZero\Filesystem\Filesystem');
    }

    /**
     * Simple checks
     */

    function it_checks_if_a_path_exists()
    {
        $this->exists($this->vfs($this->testFile))->shouldBe(true);
        unlink($this->vfs($this->testFile));
        $this->exists($this->vfs($this->testFile))->shouldBe(false);
    }

    function it_checks_if_a_path_is_a_file()
    {
        $this->isFile($this->vfs($this->testFile))->shouldBe(true);
        $this->isFile($this->vfs($this->testDirectory))->shouldBe(false);
    }

    function it_checks_if_a_path_is_a_symbolic_link()
    {
        // Can't test this truthy with vfsStream
        $this->isSymLink($this->vfs($this->testDirectory))->shouldBe(false);
    }

    function it_checks_if_a_path_is_a_directory()
    {
        $this->isDirectory($this->vfs($this->testFile))->shouldBe(false);
        $this->isDirectory($this->vfs($this->testDirectory))->shouldBe(true);
    }

    function it_checks_if_a_file_is_readable()
    {
        $this->isReadable($this->vfs($this->testFile))->shouldBe(true);
        chmod($this->vfs($this->testFile), 0000);
        $this->isReadable($this->vfs($this->testFile))->shouldBe(false);
    }

    function it_checks_if_a_directory_is_readable()
    {
        $this->isReadable($this->vfs($this->testDirectory))->shouldBe(true);
        chmod($this->vfs($this->testDirectory), 0000);
        $this->isReadable($this->vfs($this->testDirectory))->shouldBe(false);
    }

    function it_checks_if_a_file_is_writable()
    {
        $this->isWritable($this->vfs($this->testFile))->shouldBe(true);
        chmod($this->vfs($this->testFile), 0000);
        $this->isWritable($this->vfs($this->testFile))->shouldBe(false);
    }

    function it_checks_if_a_directory_is_writable()
    {
        $this->isWritable($this->vfs($this->testDirectory))->shouldBe(true);
        chmod($this->vfs($this->testDirectory), 0000);
        $this->isWritable($this->vfs($this->testDirectory))->shouldBe(false);
    }

    function it_checks_if_a_file_is_executable()
    {
        $this->isExecutable($this->vfs($this->testFile))->shouldBe(false);
        chmod($this->vfs($this->testFile), 0777);
        $this->isExecutable($this->vfs($this->testFile))->shouldBe(true);
    }

    /**
     * Get a parent directory
     */

    function it_gets_the_parent_directory()
    {
        $currentDirectory = __DIR__;
        $currentParent = substr($currentDirectory, 0, strrpos($currentDirectory, DIRECTORY_SEPARATOR));
        $this->getParentDirectory($currentDirectory)->shouldReturn($currentParent);
        $this->getParentDirectory('.parent/child')->shouldReturn('.parent');
        $this->getParentDirectory('/parent/child')->shouldReturn('/parent');
        $this->getParentDirectory('parent/child')->shouldReturn('parent');
        $this->getParentDirectory('orphan')->shouldReturn('');
        $this->getParentDirectory('./child')->shouldReturn('');
        $this->getParentDirectory($this->vfs($this->testDirectory . '/' . $this->testSubFile))->shouldReturn($this->vfs($this->testDirectory));
        $this->getParentDirectory($this->vfs($this->testFile))->shouldReturn($this->vfs());
    }

    function it_checks_if_a_directory_is_empty()
    {
        $this->createDirectory($this->vfs('newDirectory'));
        $this->isEmpty($this->vfs('newDirectory'))->shouldBe(true);
        $this->isEmpty($this->vfs($this->testDirectory))->shouldBe(false);
    }

    /**
     * List a directory
     */

    function it_lists_the_contents_of_a_directory()
    {
        $this->listDirectory($this->vfs($this->testDirectory))->shouldReturn([$this->testSubFile, $this->testSubFile2]);
    }

    function it_lists_an_empty_array_for_an_empty_directory()
    {
        vfsStream::create(['emptyDirectory' => []]);
        $this->listDirectory($this->vfs('emptyDirectory'))->shouldReturn([]);
    }

    function it_throws_if_you_list_a_directory_that_does_not_exist()
    {
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringListDirectory($this->vfs($this->testFile));
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringListDirectory($this->vfs('nonExistingDirectory'));
    }

    function it_throws_if_a_directory_can_not_be_read()
    {
        chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringListDirectory($this->vfs($this->testDirectory));
    }

    /**
     * Read a file
     */

    function it_reads_a_file()
    {
        $this->readFile($this->vfs($this->testFile))->shouldReturn($this->testFileData);
    }

    function it_throws_if_you_read_a_file_that_does_not_exist()
    {
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringReadFile($this->vfs('nonExistingFile.txt'));
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringReadFile($this->vfs($this->testDirectory));
    }

    function it_throws_if_a_file_can_not_be_read()
    {
        chmod($this->vfs($this->testFile), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringReadFile($this->vfs($this->testFile));
    }

    /**
     * Change permissions
     */

    function it_changes_file_permissions()
    {
        $this->chmod($this->vfs($this->testFile), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringReadFile($this->vfs($this->testFile));
    }

    function it_changes_directory_permissions()
    {
        $this->chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringListDirectory($this->vfs($this->testDirectory));
    }

    /**
     * Create a directory
     */

    function it_creates_a_directory()
    {
        $this->createDirectory($this->vfs($this->testDirectory))->shouldBe(true);
        $this->exists($this->vfs('newDirectory'))->shouldBe(false);
        $this->createDirectory($this->vfs('newDirectory'))->shouldBe(true);
        $this->isDirectory($this->vfs('newDirectory'))->shouldBe(true);
    }

    function it_creates_unexisting_nested_directories()
    {
        $this->exists($this->vfs('newDirectory'))->shouldBe(false);
        $this->createDirectory($this->vfs('newDirectory/subDirectory/anotherDirectory'));
        $this->isDirectory($this->vfs('newDirectory'))->shouldBe(true);
        $this->isDirectory($this->vfs('newDirectory/subDirectory'))->shouldBe(true);
        $this->isDirectory($this->vfs('newDirectory/subDirectory/anotherDirectory'))->shouldBe(true);
    }

    function it_throws_if_you_create_a_directory_but_the_path_already_exists_and_is_not_a_directory()
    {
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCreateDirectory($this->vfs($this->testFile));
    }

    function it_throws_if_creating_a_directory_fails()
    {
        chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCreateDirectory($this->vfs($this->testDirectory.'/newDirectory'));
    }

    /**
     * Create a file
     */

    function it_creates_a_file()
    {
        $this->exists($this->vfs('newFile.txt'))->shouldBe(false);
        $this->createFile($this->vfs('newFile.txt'), 'newContent');
        $this->isFile($this->vfs('newFile.txt'))->shouldBe(true);
    }

    function it_creates_a_file_in_unexisting_directories()
    {
        $this->exists($this->vfs('newDirectory/subDirectory/newFile.txt'))->shouldBe(false);
        $this->createFile($this->vfs('newDirectory/subDirectory/newFile.txt'), 'newContent');
        $this->isFile($this->vfs('newDirectory/subDirectory/newFile.txt'))->shouldBe(true);
    }

    function it_creates_a_file_over_an_existing_file_only_if_you_allow_overwrite()
    {
        $this->createFile($this->vfs($this->testFile), 'content', true);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCreateFile($this->vfs($this->testFile), 'content', false);
    }

    function it_throws_if_you_attempt_to_create_a_file_but_its_parent_is_not_a_directory()
    {
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCreateFile($this->vfs($this->testFile.'/newFile.txt'), 'newContent');
    }

    function it_throws_if_creating_a_file_fails()
    {
        chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCreateFile($this->vfs($this->testDirectory.'/newFile.txt'), 'newContent');
    }

    /**
     * Delete a directory
     */

    function it_deletes_a_directory()
    {
        $this->createDirectory($this->vfs('newDirectory'));
        $this->exists($this->vfs('newDirectory'))->shouldBe(true);
        $this->delete($this->vfs('newDirectory'))->shouldBe(true);
        $this->exists($this->vfs('newDirectory'))->shouldBe(false);
    }

    function it_deletes_a_directory_recursively()
    {
        $this->exists($this->vfs($this->testDirectory))->shouldBe(true);
        $this->delete($this->vfs($this->testDirectory), true)->shouldBe(true);
        $this->exists($this->vfs($this->testDirectory))->shouldBe(false);
    }

    function it_does_not_delete_a_directory_recursively_if_you_do_not_allow_it()
    {
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringDelete($this->vfs($this->testDirectory));
    }

    function it_throws_if_deleting_a_directory_fails()
    {
        $this->createDirectory($this->vfs('newDirectory/test'));
        chmod($this->vfs('newDirectory'), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringDelete($this->vfs('newDirectory/test'));
    }

    /**
     * Delete a file
     */

    function it_deletes_a_file()
    {
        $this->exists($this->vfs($this->testFile))->shouldBe(true);
        $this->delete($this->vfs($this->testFile))->shouldBe(true);
        $this->exists($this->vfs($this->testFile))->shouldBe(false);
    }

    function it_returns_true_if_you_delete_a_file_that_does_not_exist()
    {
        $this->exists($this->vfs('nonExisting.txt'))->shouldBe(false);
        $this->delete($this->vfs($this->testFile))->shouldBe(true);
    }

    function it_throws_if_deleting_a_file_fails()
    {
        chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringDelete($this->vfs($this->testDirectory.'/'.$this->testSubFile));
    }

    /**
     * Rename a file or directory
     */

    function it_renames_a_file_or_directory()
    {
        $this->exists($this->vfs($this->testFile))->shouldBe(true);
        $this->exists($this->vfs('renamedFile.txt'))->shouldBe(false);
        $this->rename($this->vfs($this->testFile), $this->vfs('renamedFile.txt'))->shouldBe(true);
        $this->exists($this->vfs($this->testFile))->shouldBe(false);
        $this->exists($this->vfs('renamedFile.txt'))->shouldBe(true);
    }

    function it_renames_and_overwrites_an_existing_name_only_if_you_allow_it()
    {
        $this->rename($this->vfs($this->testFile), $this->vfs($this->testDirectory.'/'.$this->testSubFile), true)->shouldBe(true);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringRename($this->vfs($this->testFile), $this->vfs($this->testDirectory.'/'.$this->testSubFile));
    }

    function it_throws_if_renaming_fails()
    {
        chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringRename($this->vfs($this->testFile), $this->vfs($this->testDirectory.'/'.$this->testSubFile), true);
    }

    /**
     * Copy a file
     */

    function it_copies_a_file()
    {
        $this->exists($this->vfs('duplicate.txt'))->shouldBe(false);
        $this->copy($this->vfs($this->testFile), $this->vfs('duplicate.txt'));
        $this->exists($this->vfs('duplicate.txt'))->shouldBe(true);
    }

    function it_copies_a_file_into_a_directory_if_the_destination_path_is_a_directory()
    {
        $this->copy($this->vfs($this->testFile), $this->vfs($this->testDirectory));
        $this->exists($this->vfs($this->testDirectory.'/'.$this->testFile))->shouldBe(true);
    }

    function it_copies_a_file_into_a_new_directory()
    {
        $this->copy($this->vfs($this->testFile), $this->vfs('newDirectory/subDirectory/newFile.txt'));
        $this->exists($this->vfs('newDirectory/subDirectory/newFile.txt'))->shouldBe(true);
    }

    function it_copies_a_file_to_an_existing_destination_only_if_you_allow_overwrite()
    {
        $this->copy($this->vfs($this->testFile), $this->vfs($this->testDirectory.'/'.$this->testSubFile), true)->shouldBe(true);
        $this->readFile($this->vfs($this->testDirectory.'/'.$this->testSubFile))->shouldReturn($this->testFileData);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCopy($this->vfs($this->testFile), $this->vfs($this->testDirectory.'/'.$this->testSubFile));
    }

    function it_throws_if_copying_a_file_fails()
    {
        chmod($this->vfs($this->testDirectory), 0000);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCopy($this->vfs($this->testFile), $this->vfs($this->testDirectory.'/newFile.txt'));
    }

    /**
     * Copy a directory
     */

    function it_copies_a_whole_directory()
    {
        $this->copy($this->vfs($this->testDirectory), $this->vfs('newDirectory'));
        $this->isDirectory($this->vfs('newDirectory'))->shouldBe(true);
        $this->isFile($this->vfs('newDirectory/'.$this->testSubFile))->shouldBe(true);
        $this->isFile($this->vfs('newDirectory/'.$this->testSubFile2))->shouldBe(true);
    }

    function it_copies_a_directory_to_an_existing_destination_only_if_you_allow_overwrite()
    {
        $this->createDirectory($this->vfs('newDirectory'));
        $this->createFile($this->vfs('newDirectory/'.$this->testSubFile), '');
        $this->createFile($this->vfs('newDirectory/'.$this->testSubFile2), '');
        $this->copy($this->vfs($this->testDirectory), $this->vfs('newDirectory'), true)->shouldBe(true);
        $this->readFile($this->vfs('newDirectory/'.$this->testSubFile))->shouldReturn($this->testSubFileData);
        $this->readFile($this->vfs('newDirectory/'.$this->testSubFile2))->shouldReturn($this->testSubFileData2);
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCopy($this->vfs($this->testDirectory), $this->vfs('newDirectory'));
    }

    function it_throws_if_you_attempt_to_copy_an_unexisting_file_or_directory()
    {
        $this->shouldThrow('CodeZero\Filesystem\IOException')->duringCopy($this->vfs('unExisting'), $this->vfs('any'));
    }

    /**
     * Get the virtual filesystem path
     *
     * @param string $path
     *
     * @return string
     */
    private function vfs($path = null)
    {
        $fullPath = $path
            ? $this->baseDirectory.'/'.$path
            : $this->baseDirectory;

        return VfsStream::url($fullPath);
    }
}
