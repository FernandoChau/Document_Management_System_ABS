// import React from "react";
import Button from "../ui/button/Button";
import { ComputerDesktopIcon } from "@heroicons/react/24/outline";

function UserDevices() {
    interface DeviceData {
        id:Number,
        navigator: string,
        ipAdderess: string
    }

    const devices: DeviceData[] =[ 
        {
            id:1,
            navigator: "Chrome",
            ipAdderess: "Esse Despositivo!",
        },
        {
            id:2,
            navigator: "Edge",
            ipAdderess: "127.0.0.1",
        },
        {
            id:3,
            navigator: "Brave",
            ipAdderess: "192.110.20.1",
        }
    ]

    return (
        <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
            <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
                        Dispositivos Com a Sessão Iniciada
                    </h4>

                    <div className="flex gap-2">
                        <div>
                            <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                Titula Da Seccao
                            </p>
                            <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                                Corpo da Seccao
                            </p>
                        </div>
                    </div>
                </div>

                <div className="felx items-center justify-center">
                    {devices.map((device) => 
                    <div className="px-4 bg-gray-50 py-2 rounded-xl mb-2">
                        <div className=" text-gray-400 flex items-center justify-between gap-1">
                            <span className="flex items-center gap-2 min-w-60">
                                <ComputerDesktopIcon className="size-10" />
                                <span>
                                    <h2>{device.navigator}</h2>
                                    <p className="font-light text-theme-xs">
                                        {device.ipAdderess}
                                    </p>
                                </span>
                            </span>
                            <Button className="h-8">Encerar</Button>
                        </div>
                    </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default UserDevices;
