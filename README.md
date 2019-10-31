#coordtransform 坐标转换

#### 参考
    https://github.com/wandergis/coordtransform 
    https://github.com/qichengzx/coordtransform


#坐标系相关

	WGS84坐标系：即地球坐标系，国际上通用的坐标系。
	GCJ02坐标系：即火星坐标系，WGS84坐标系经加密后的坐标系。Google Maps，高德在用。
	BD09坐标系：即百度坐标系，GCJ02坐标系经加密后的坐标系。
	lat 纬度 log经度
	
#安装

    composer require songbai/coord


#快速开始

    use SongBai\Coord\Transform;
    
    $tool = new Transform();
    
    //BD09toGCJ02 百度坐标系->火星坐标系
    $tool->BD09toGCJ02($lon,$lat); //return array($lon,$lat);
    
    //GCJ02toBD09 火星坐标系->百度坐标系
    $tool->GCJ02toBD09($lon,$lat);//return array($lon,$lat);
    
    //WGS84toGCJ02 WGS84坐标系->火星坐标系
    $tool->WGS84toGCJ02($lon,$lat);//return array($lon,$lat);