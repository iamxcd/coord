<?php

namespace SongBai\Coord;

class Transform
{

	// WGS84坐标系：即地球坐标系，国际上通用的坐标系。
	// GCJ02坐标系：即火星坐标系，WGS84坐标系经加密后的坐标系。Google Maps，高德在用。
	// BD09坐标系：即百度坐标系，GCJ02坐标系经加密后的坐标系。
	// lat 纬度 log经度

	const X_PI   = M_PI * 3000.0 / 180.0;
	const OFFSET = 0.00669342162296594323;
	const AXIS   = 6378245.0;


	//BD09toGCJ02 百度坐标系->火星坐标系
	public function BD09toGCJ02(float $lon, float $lat): array
	{
		$x = $lon - 0.0065;
		$y = $lat - 0.006;

		$z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * self::X_PI);
		$theta = atan2($y, $x) - 0.000003 * cos($x * self::X_PI);

		$gLon = $z * cos($theta);
		$gLat = $z * sin($theta);

		return [
			'lon' => $gLon,
			'lat' => $gLat
		];
	}

	//GCJ02toBD09 火星坐标系->百度坐标系
	public function GCJ02toBD09(float $lon, float $lat): array
	{
		$z = sqrt($lon * $lon + $lat * $lat) + 0.00002 * sin($lat * self::X_PI);
		$theta = atan2($lat, $lon) + 0.000003 * cos($lon * self::X_PI);

		$bdLon = $z * cos($theta) + 0.0065;
		$bdLat = $z * sin($theta) + 0.006;

		return [
			'lon' => $bdLon,
			'lat' => $bdLat
		];
	}

	//WGS84toGCJ02 WGS84坐标系->火星坐标系
	public function WGS84toGCJ02(float $lon, float $lat): array
	{
		if ($this->isOutOFChina($lon, $lat)) {
			return [
				'lon' => $lon,
				'lat' => $lat
			];
		}
		return $this->delta($lon, $lat);
	}

	//GCJ02toWGS84 火星坐标系->WGS84坐标系
	public function GCJ02toWGS84(float $lon, float $lat): array
	{
		if ($this->isOutOFChina($lon, $lat)) {
			return [
				'lon' => $lon,
				'lat' => $lat
			];
		}
		$coord = $this->delta($lon, $lat);

		return [
			'lon' => $lon * 2 - $coord['lon'],
			'lat' => $lat * 2 - $coord['lat']
		];
	}

	//BD09toWGS84 百度坐标系->WGS84坐标系
	public function BD09toWGS84(float $lon, float $lat): array
	{
		$coord = $this->BD09toGCJ02($lon, $lat);
		return $this->GCJ02toWGS84($coord['lon'], $coord['lat']);
	}

	//WGS84toBD09 WGS84坐标系->百度坐标系
	public function WGS84toBD09(float $lon, float $lat): array
	{
		$coord = $this->WGS84toGCJ02($lon, $lat);
		return $this->GCJ02toBD09($coord['lon'], $coord['lat']);
	}

	public function delta(float $lon, float $lat): array
	{
		$xy = $this->transform($lon - 105.0, $lat - 35.0);
		$dlat = $xy['x'];
		$dlon = $xy['y'];

		$radlat = $lat / 180.0 * M_PI;
		$magic = sin($radlat);
		$magic = 1 - self::OFFSET * $magic * $magic;
		$sqrtmagic = sqrt($magic);

		$dlat = ($dlat * 180.0) / ((self::AXIS * (1 - self::OFFSET)) / ($magic * $sqrtmagic) * M_PI);
		$dlon = ($dlon * 180.0) / (self::AXIS / $sqrtmagic * cos($radlat) * M_PI);

		$mgLat = $lat + $dlat;
		$mgLon = $lon + $dlon;
		return [
			'lon' => $mgLon,
			'lat' => $mgLat
		];
	}
	public function transform(float $lon, float $lat): array
	{
		$lonlat = $lon * $lat;
		$absX = sqrt(abs($lon));
		$lonPi = $lon * M_PI;
		$latPi = $lat * M_PI;
		$d = 20.0 * sin(6.0 * $lonPi) + 20.0 * sin(2.0 * $lonPi);
		$x = $d;
		$y = $d;
		$x += 20.0 * sin($latPi) + 40.0 * sin($latPi / 3.0);
		$y += 20.0 * sin($lonPi) + 40.0 * sin($lonPi / 3.0);
		$x += 160.0 * sin($latPi / 12.0) + 320 * sin($latPi / 30.0);
		$y += 150.0 * sin($lonPi / 12.0) + 300.0 * sin($lonPi / 30.0);
		$x *= 2.0 / 3.0;
		$y *= 2.0 / 3.0;
		$x += -100.0 + 2.0 * $lon + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lonlat + 0.2 * $absX;
		$y += 300.0 + $lon + 2.0 * $lat + 0.1 * $lon * $lon + 0.1 * $lonlat + 0.1 * $absX;
		return [
			'x' => $x,
			'y' => $y
		];
	}

	public function isOutOFChina(float $lon, float $lat): bool
	{
		return !($lon > 72.004 && $lon < 135.05 && $lat > 3.86 && $lat < 53.55);
	}
}
