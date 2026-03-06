<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Model\ResourceModel;

/**
 * Cms page mysql resource
 */
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ves_vendor_message', 'message_id');
    }

    /**
     * Get First Message Detail
     *
     * @param \Vnecoms\VendorsMessage\Model\Message $message
     * @return \Vnecoms\VendorsMessage\Model\Message\Detail
     */
    public function getFirstMessageDetail(
        \Vnecoms\VendorsMessage\Model\Message $message
    ) {
        $table = $this->getTable('ves_vendor_message_detail');
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(
            $table,
            [
                'detail_id',
            ]
        )->where(
            'message_id = :message_id'
        )->order(
            'detail_id ASC'
        )->limit(1);
        $bind = [
            'message_id' => $message->getId(),
        ];
        $detailId = $connection->fetchOne($select,$bind);

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $detail = $om->create('Vnecoms\VendorsMessage\Model\Message\Detail');
        $detail->load($detailId);

        return $detail;
    }

    /**
     * Get Last Message Detail
     *
     * @param \Vnecoms\VendorsMessage\Model\Message $message
     * @return \Vnecoms\VendorsMessage\Model\Message\Detail
     */
    public function getLastMessageDetail(
        \Vnecoms\VendorsMessage\Model\Message $message
    ) {
        $table = $this->getTable('ves_vendor_message_detail');
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(
            $table,
            [
                'detail_id',
            ]
        )->where(
            'message_id = :message_id'
        )->order(
            'detail_id DESC'
        )->limit(1);
        $bind = [
            'message_id' => $message->getId(),
        ];
        $detailId = $connection->fetchOne($select,$bind);

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $detail = $om->create('Vnecoms\VendorsMessage\Model\Message\Detail');
        $detail->load($detailId);

        return $detail;
    }

    /**
     * Mark all detail message as read
     *
     * @param \Vnecoms\VendorsMessage\Model\Message $message
     * @return Zend_Db_Statement_Interface
     */
    public function markAsRead(
        \Vnecoms\VendorsMessage\Model\Message $message
    ) {
        $adapter   = $this->getConnection();
        $sql = "UPDATE ".$this->getTable('ves_vendor_message_detail')
            .' SET is_read=1'
            .' WHERE message_id ='.$message->getId()
            .' AND is_read=0';

        return $adapter->query($sql);
    }
}
