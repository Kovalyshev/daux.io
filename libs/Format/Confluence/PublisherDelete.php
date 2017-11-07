<?php namespace Todaymade\Daux\Format\Confluence;

class PublisherDelete
{
    /**
     * @var \Symfony\Component\Console\Output\Output
     */
    public $output;

    /**
     * @var array files that can be deleted
     */
    protected $deletable;

    /**
     * @var boolean should delete ?
     */
    protected $delete;

    /**
     * @var Api
     */
    protected $client;

    public function __construct($output, $delete, $client)
    {
        $this->output = $output;
        $this->delete = $delete;
        $this->client = $client;
    }

    protected function listDeletable($published, $prefix = '')
    {
        foreach ($published['children'] as $child) {
            if (array_key_exists('children', $child) && count($child['children'])) {
                $this->listDeletable($child, $child['title'] . '/');
            }

            if (!array_key_exists('needed', $child)) {
                $this->deletable[$child['id']] = $prefix . $child['title'];
            }
        }
    }

    public function handle($published)
    {
        $this->listDeletable($published);

        if (!count($this->deletable)) {
            return;
        }

        if ($this->delete) {
            $this->doDelete();

        } else {
            $this->displayDeletable();
        }
    }

    protected function doDelete() {
        $this->output->writeLn('Deleting obsolete pages...');
        foreach ($this->deletable as $id => $title) {
            $this->output->writeLn("- $title");
            $this->client->deletePage($id);
        }
    }

    protected function displayDeletable() {
        $this->output->writeLn('Listing obsolete pages...');
        $this->output->writeLn("> The following pages will not be deleted, but just listed for information.");
        $this->output->writeLn("> If you want to delete these pages, you need to set the --delete flag on the command.");
        foreach ($this->deletable as $id => $title) {
            $this->output->writeLn("- $title");
        }
    }
}
