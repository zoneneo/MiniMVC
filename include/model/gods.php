<?php
	class gods extends Model{
		public function onerd($id)
		{
			global $fields;	
			$fields= $this->dsql->GetOne("select * from #@__goods where id='$id'");
			return $fields;
		}
		public function insert($query){
			return $this->dsql->ExecuteNoneQuery2($query);
		}
		public function remove($id){
			
			return $this->dsql->ExecuteNoneQuery2("delete from sun_goods where id='".$id."'");			
		}
		public function update($query){
			
			return $this->dsql->ExecuteNoneQuery2($query );			
		}
	}
?>